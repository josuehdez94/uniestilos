<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/backend/fotografias")
 */
class ArticuloFotografiaController extends AbstractController
{
    /**
     * @Route("/subir/{articulo_id}", name="backend_articulo_fotografia", methods={"POST"})
     */
    public function nuevaFotografia(Request $request, $articulo_id)
    {
       $entityManager = $this->getDoctrine()->getManager();
       $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneById($articulo_id);
       if (!$articulo){
           throw $this->createNotFoundException('Articulo no encontrado.');
       }
      
        $erroresTipo = [];
        $erroresTamaño = [];
        foreach ($request->files->get('fotografias') as $foto){
           $tipo = $foto->getMimeType();
           $temp = $foto->getPathName();
           if($tipo == 'image/jpeg' || $tipo == 'image/jpg' || $tipo == 'image/png'){
                //count($request->files->get('fotografias')) > 1 ?  $nombreArchivo = $_FILES['fotografias']['name'][1] : $nombreArchivo = $_FILES['fotografias']['name'];
                $nombreArchivo = $_FILES['fotografias']['name'][0];
                list($ancho, $alto, $extension) = getimagesize($temp);
               /* if ($ancho > 300 && $ancho < 1000){
                    if ($alto > 600 && $alto < 1000){ */
                        $nombreArchivoBd = hash('md5', date('Y-m-d g:i:s').random_int(0, 4000000)).'.'.substr($nombreArchivo,strrpos($nombreArchivo,'.')+1);
                        $this->subirFotografias($temp, $nombreArchivoBd);
                        $fotografia = new \App\Entity\ArticuloFotografia();
                        $fotografia->setArticulo($articulo);
                        $fotografia->setUsuarioSubio($this->getUser());
                        $fotografia->setFechaHoraCreacion(new \DateTime());
                        $fotografia->setNombreArchivo($nombreArchivoBd);
                        $entityManager->persist($fotografia);
                        $entityManager->flush();
                       
                   /*  }else{
                        $erroresTamaño [] = [
                            'archivo' => $nombreArchivo,
                            'alto' => $alto,
                            'error' => 'El alto de esta imagen no es el correcto'
                        ];
                    }
               }else{
                   $erroresTamaño [] = [
                       'archivo' => $nombreArchivo,
                       'ancho' => $ancho,
                       'error' => 'El ancho de esta imagen no es el correcto'
                   ];
               }*/
           }/* else{
                $erroresTipo [] = [
                    'archivo' => $temp,
                    'tipo' => $tipo,
                    'error' => 'El tipo imagen no es el correcto'
                ];
           }  */

        }
        $this->addFlash('Editado', 'Se han añadido nuevas fotografias');
        return $this->redirectToRoute('backend_articulo_fotografia_editar', [
            'id' => $articulo->getId()
        ]);
       
    }

    /**
     * @Route("/editar/{id}", name="backend_articulo_fotografia_editar", methods={"GET", "POST"})
     */
    public function editarFotografias($id){
        $entityManager = $this->getDoctrine()->getManager();
        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneBy(['id' => $id]);
        if(!$articulo){
            throw $this->createNotFoundException('Articulo no encontrado');
        }
        return $this->render('backend/ArticuloFotografia/editarFotografias.html.twig', [
            'articulo' => $articulo,
            'submenu' => 'fotografias',
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="backend_articulo_fotografia_eliminar", methods={"POST"})
     */
    public function eliminarFotografia($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $foto = $entityManager->getRepository(\App\Entity\ArticuloFotografia::class)->findOneById($id);
        if (!$foto){
            throw $this->createNotFoundException('Foto no encontrada.');
        }
        $entityManager->remove($foto);
        $entityManager->flush();
        $this->addFlash('Eliminado', 'Fotografia eliminada correctamente');
        return $this->redirectToRoute('backend_articulo_editar', [
            'id' => $foto->getArticulo()->getId()
        ]);
    }

    /**
     * @Route("/fotografia-principal/{id}", name="backend_articulo_fotografia_principal", methods={"POST"})
     */
    public function fotografiaPrincipal($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $foto = $entityManager->getRepository(\App\Entity\ArticuloFotografia::class)->findOneById($id);
        if (!$foto){
            throw $this->createNotFoundException('Foto no encontrada.');
        }
        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneById($foto->getArticulo()->getId());
        $articulo->setFotografiaPrincipal($foto);
        $entityManager->flush();
        $this->addFlash('Editado', 'Fotografia principal establecida correctamente');
        return $this->redirectToRoute('backend_articulo_editar', [
            'id' => $foto->getArticulo()->getId()
        ]);
    }

    /**
     * funcion para subir imagenes al servidor
     */
    public function subirFotografias($temp, $nombreArchivo)
    {

        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        //$extension = $this->getNombreArchivo()->guessExtension();
        //$name_image = sha1($this->id);
        //echo "nombre de imagen en upload(): $name_image.$extension <br/>";
        //echo "comenzando carga de archivo";
        //$this->getNombreArchivoFile()->move($this->getUploadRootDirNombreArchivo(), $this->nombreArchivo);
        //echo "carga del archivo finalizada";
        //$this->setFile(null);
        if(move_uploaded_file($temp, $this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo)){
            
        }else{
            echo 'Ocurrió algún error al subir la imagen';
        }

        ## normal
        $max_h = 1024;
        $max_w = 1024;

        // thumbnail
        $thumb_max_h = 200;
        $thumb_max_w = 200;

        // mini-thumbnail
        $mini_thumb_max_h = 75;
        $mini_thumb_max_w = 75;

        // make banner
        try {
            //new \Imagick;
            //move_uploaded_file($newFile, $this->getAbsoluteNombreArchivo());
            $img = new \Imagick($this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo);
            //$img->resampleImage(72, 72, 1, 1);
            $img->scaleImage($max_w, 0);
            $img->setImageFormat('jpg');
            $img->setImageCompression(\imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(80);
            $img->stripimage();
            //$img = $img->flattenimages();
            $img->setimagebackgroundcolor('white');

            // corregir orientacion

            $orientation = $img->getImageOrientation();

            switch ($orientation) {
                case \imagick::ORIENTATION_BOTTOMRIGHT:
                    $img->rotateimage("#000", 180); // rotate 180 degrees
                    break;

                case \imagick::ORIENTATION_RIGHTTOP:
                    $img->rotateimage("#000", 90); // rotate 90 degrees CW
                    break;

                case \imagick::ORIENTATION_LEFTBOTTOM:
                    $img->rotateimage("#000", -90); // rotate 90 degrees CCW
                    break;
            }

            // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
            $img->setImageOrientation(\imagick::ORIENTATION_TOPLEFT);

            $d = $img->getImageGeometry();

            $thumbnail = clone $img;
            $thumbnail->thumbnailImage($thumb_max_w, null);

            $mini_thumbnail = clone $img;
            $mini_thumbnail->thumbnailImage($mini_thumb_max_w, null);

            $h = $d['height'];
            $w = $d['width'];

            if ($h > $max_h) {
                $img->scaleImage(0, $max_h);
                $img->writeImage($this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo);
                $thumbnail->thumbnailImage(null, $thumb_max_h);
                $thumbnail->writeImage($this->getUploadRootDirNombreArchivo().'/thumbs/'.$nombreArchivo);
                $mini_thumbnail->thumbnailImage(null, $mini_thumb_max_h);
                $mini_thumbnail->writeImage($this->getUploadRootDirNombreArchivo().'/mini_thumbs/'.$nombreArchivo);
            } else {
                $img->writeImage($this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo);
                $thumbnail->writeImage($this->getUploadRootDirNombreArchivo().'/thumbs/'.$nombreArchivo);
                $mini_thumbnail->writeImage($this->getUploadRootDirNombreArchivo().'/mini_thumbs/'.$nombreArchivo);
            }
            $img->destroy();
            $thumbnail->destroy();
            $mini_thumbnail->destroy();
            //echo "Imagen en: " . $this->getMiniThumbnailNombreArchivo();
            //echo "Proceso finalizado." . '</br>';
            //exit();
        } catch (Exception $ex) {
            echo $ex . '</br>';
        }
       

    }

    protected function getUploadRootDirNombreArchivo() {
        // the absolute directory picture where uploaded
        // documents should be saved
        return '/var/www/tiendaonline/market/project/public' . $this->getUploadDirNombreArchivo();
    }

    protected function getUploadDirNombreArchivo() {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/assets/img/back/articulos';
    }
}

<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;

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
                        $this->subirFotografias2($temp, $nombreArchivoBd);
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
    public function editarFotografias(Request $request, PaginatorInterface $paginator, $id){
        $entityManager = $this->getDoctrine()->getManager();
        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneBy(['id' => $id]);
        if(!$articulo){
            throw $this->createNotFoundException('Articulo no encontrado');
        }
        $fotografias = $paginator->paginate(
            $entityManager->getRepository(\App\Entity\ArticuloFotografia::class)->findAll(),
            $request->query->getInt('pagina', 1),
            3
        );
        if($request->isXmlHttpRequest()){
            return new Response(json_encode([
                'type' => 'load',
                'content' => $this->renderView('backend/ArticuloFotografia/cargarFotografias.html.twig', [
                    'articulo' => $articulo,
                    'fotografias' => $fotografias,
                    'submenu' => 'fotografias',
                ])
            ]));
        }
        return $this->render('backend/ArticuloFotografia/editarFotografias.html.twig', [
            'articulo' => $articulo,
            'fotografias' => $fotografias,
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
        return $this->redirectToRoute('backend_articulo_fotografia_editar', [
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
        return $this->redirectToRoute('backend_articulo_fotografia_editar', [
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
        } catch (\Exception $ex) {
            echo $ex . '</br>';
        }
       

    }

    /**
     * funcion para subir imagenes al servidor
     */
    public function subirFotografias2($temp, $nombreArchivo)
    {
        #crear carpetas si no existen
        if (!is_dir($this->getUploadRootDirNombreArchivo())) {
            mkdir($this->getUploadRootDirNombreArchivo(), 0777, true);
        }
        #crear carpetas si no existen
        if (!is_dir($this->getWaterImageXL())) {
            mkdir($this->getWaterImageXL(), 0777, true);
        }
        #crear carpetas si no existen
        if (!is_dir($this->getImageXL())) {
            mkdir($this->getImageXL(), 0777, true);
        }
        #crear carpetas si no existen
        if (!is_dir($this->getImageL())) {
            mkdir($this->getImageL(), 0777, true);
        }
        #crear carpetas si no existen
        if (!is_dir($this->getImageM())) {
            mkdir($this->getImageM(), 0777, true);
        }
        #crear carpetas si no existen
        if (!is_dir($this->getImageS())) {
            mkdir($this->getImageS(), 0777, true);
        }

        if(move_uploaded_file($temp, $this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo)){
            
        }else{
            echo 'Ocurrió algún error al subir la imagen';
        }
        ## xl
        $img_xl_max_height = 2048;
        $img_xl_max_width = 2048;

        // mediana
        $img_l_max_height = 512;
        $img_l_max_width = 512;

        // thumbnail
        $img_m_max_height = 256;
        $img_m_max_width = 256;

        // mini-thumbnail
        $img_s_max_height = 64;
        $img_s_max_width = 64;
        // make banner
        try {
            //new \Imagick;
            //move_uploaded_file($newFile, $this->getAbsoluteNombreArchivo());
            $img_xl = new \Imagick($this->getUploadRootDirNombreArchivo().'/'.$nombreArchivo);
            //$img->resampleImage(72, 72, 1, 1);
            //$img->scaleImage($img_xl_max_width, 0);
            $img_xl->setImageFormat('jpg');
            $img_xl->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $img_xl->setImageCompressionQuality(70);
            $img_xl->stripimage();
            $img_xl = $img_xl->flattenimages();
            $img_xl->setimagebackgroundcolor('white');

            // corregir orientacion
            $orientation = $img_xl->getImageOrientation();
            switch ($orientation) {
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $img_xl->rotateimage("#000", 180); // rotate 180 degrees
                    break;

                case \Imagick::ORIENTATION_RIGHTTOP:
                    $img_xl->rotateimage("#000", 90); // rotate 90 degrees CW
                    break;

                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $img_xl->rotateimage("#000", -90); // rotate 90 degrees CCW
                    break;
            }

            // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
            $img_xl->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);

            $dimensiones_img_xl = $img_xl->getImageGeometry();

            $img_l = clone $img_xl;
//            $thumbnail->thumbnailImage($img_small_max_width, null);

            $img_m = clone $img_xl;
            $img_m->thumbnailImage($img_l_max_width, null);

            $img_s = clone $img_xl;
//            $mini_thumbnail->thumbnailImage($img_xs_max_width, null);

            $original_height = $dimensiones_img_xl['height'];
            $original_width = $dimensiones_img_xl['width'];
            if ($original_height > $img_xl_max_height) {
                $img_xl->scaleImage(null, $img_xl_max_height);
                $img_xl->writeImage($this->getImageXL().'/'.$nombreArchivo);
                $img_xl->writeImage($this->getWaterImageXL().'/'.$nombreArchivo);                
                $this->colocaMarcaAgua($this->getWaterImageXL().'/'.$nombreArchivo);
                
                $img_l->thumbnailImage(null, $img_l_max_height);
                $img_l->writeImage($this->getImageL().'/'.$nombreArchivo);
                $this->colocaMarcaAgua($this->getImageL().'/'.$nombreArchivo);
                
                $img_m->thumbnailImage(null, $img_m_max_height);
                $img_m->writeImage($this->getImageM().'/'.$nombreArchivo);
                
                $img_s->thumbnailImage(null, $img_s_max_height);
                $img_s->writeImage($this->getImageS().'/'.$nombreArchivo);
            } else if ($original_width > $img_xl_max_width) {
                $img_xl->scaleImage($img_xl_max_width, null);
                $img_xl->writeImage($this->getImageXL().'/'.$nombreArchivo);
                $img_xl->writeImage($this->getWaterImageXL().'/'.$nombreArchivo);
                $this->colocaMarcaAgua($this->getWaterImageXL().'/'.$nombreArchivo);
                
                $img_l->thumbnailImage($img_l_max_width, null);
                $img_l->writeImage($this->getImageL().'/'.$nombreArchivo);
                $this->colocaMarcaAgua($this->getImageL().'/'.$nombreArchivo);
                
                $img_m->thumbnailImage($img_m_max_width, null);
                $img_m->writeImage($this->getImageM().'/'.$nombreArchivo);
                
                $img_s->thumbnailImage($img_s_max_width, null);
                $img_s->writeImage($this->getImageS().'/'.$nombreArchivo);
            } else {
                $img_xl->writeImage($this->getImageXL().'/'.$nombreArchivo);
                $img_xl->writeImage($this->getWaterImageXL().'/'.$nombreArchivo);
                $this->colocaMarcaAgua($this->getWaterImageXL().'/'.$nombreArchivo);
                
                $img_l->writeImage($this->getImageL().'/'.$nombreArchivo);
                $this->colocaMarcaAgua($this->getImageL().'/'.$nombreArchivo);
                
                $img_m->writeImage($this->getImageM().'/'.$nombreArchivo);
                $img_s->writeImage($this->getImageS().'/'.$nombreArchivo);
            }
            $img_xl->destroy();
            $img_l->destroy();
            $img_m->destroy();
            $img_s->destroy();
        } catch (\Exception $ex) {
            throw $ex . '</br>';
        }
    }

    protected function getUploadRootDirNombreArchivo() {
        // the absolute directory picture where uploaded
        // documents should be saved
        if($_SERVER['SERVER_NAME'] == '127.0.0.1:8000' || $_SERVER['SERVER_NAME'] == '127.0.0.1'){
            $dir = '/var/www/tiendaonline/market/project/private';
        }elseif($_SERVER['SERVER_NAME'] == 'dev.uniestilos.shop'){
            $dir = '/home/u141948896/public_html/dev/uniestilos/private';
        }elseif($_SERVER['SERVER_NAME'] == 'uniestilos.shop'){
            $dir = '/home/u141948896/public_html/uniestilos/private';
        }
        return $dir . $this->getUploadDirNombreArchivo();
    }

    protected function getUploadDirNombreArchivo() {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/articulos';
    }

    ############################################################################
    # INICIAN RUTAS ABSOLUTE

    private function getWaterImageXL() {
        return $this->getUploadRootDirNombreArchivo() . '/xl_wm';
    }
    
    private function getImageXL() {
        return $this->getUploadRootDirNombreArchivo() . '/xl';
    }

    private function getImageL() {
        return $this->getUploadRootDirNombreArchivo() . '/l';
    }       

    private function getImageM() {
        return $this->getUploadRootDirNombreArchivo() . '/m';
    }
    
    private function getImageS() {
        return $this->getUploadRootDirNombreArchivo() . '/s';
    }


    /**
     * funcion para crear marca de agua en las imagenes
     * 
     */
    public function colocaMarcaAgua($ruta_imagen) {
        $watermark = new \Imagick();     
        $watermark->setBackgroundColor(new \ImagickPixel('transparent'));
        $watermark->readImage($this->getUploadRootDirNombreArchivo() . '/copyright.png');
        $watermark->setImageFormat('png32');
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.15, \Imagick::CHANNEL_ALPHA);
        
        $img = new \Imagick($ruta_imagen);
        $img->compositeImage($watermark, \imagick::COMPOSITE_OVER, 0, 0);
        $img->writeImage($ruta_imagen);        
    }
    
}

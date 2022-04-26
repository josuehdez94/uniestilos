<?php

namespace App\Entity;

use App\Repository\ArticuloFotografiaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ArticuloFotografiaRepository::class)
 * @UniqueEntity("nombre_archivo")
 * @ORM\HasLifecycleCallbacks()
 */
class ArticuloFotografia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="fecha_hora_creacion" , type="datetime")
     *
     */
    private $fechaHoraCreacion;

    /**
     *
     * @ORM\Column(name="nombre_archivo", type="string", length=100, unique=true, nullable=true)
     */
    private $nombreArchivo;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_creo_id", referencedColumnName="id")
     * })
     */
    private $usuarioSubio;

    /**
     *  @var \Articulo
     *
     * @ORM\ManyToOne(targetEntity="Articulo", inversedBy="fotografias")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="articulo_id", referencedColumnName="id", nullable=false)
     * })
     *
     */
    private $articulo;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFechaHoraCreacion()
    {
        return $this->fechaHoraCreacion;
    }

    /**
     * @param mixed $fechaHoraCreacion
     */
    public function setFechaHoraCreacion($fechaHoraCreacion): void
    {
        $this->fechaHoraCreacion = $fechaHoraCreacion;
    }

    /**
     * @return mixed
     */
    public function getNombreArchivo()
    {
        return $this->nombreArchivo;
    }

    /**
     * @param mixed $nombreArchivo
     */
    public function setNombreArchivo($nombreArchivo): void
    {
        $this->nombreArchivo = $nombreArchivo;
    }

    /**
     * @return \User
     */
    public function getUsuarioSubio()
    {
        return $this->usuarioSubio;
    }

    /**
     * @param \User $usuarioSubio
     */
    public function setUsuarioSubio($usuarioSubio): void
    {
        $this->usuarioSubio = $usuarioSubio;
    }

    /**
     * @return \Articulo
     */
    public function getArticulo()
    {
        return $this->articulo;
    }

    /**
     * @param \Articulo $articulo
     */
    public function setArticulo($articulo): void
    {
        $this->articulo = $articulo;
    }

    ######################Funciones Fotografias #################################

    /**
     * funciona para obtener base64_encode para las imagenes de fotografia
     */
    public function getPathArchivo($carpeta, $archivo_fotografia) {
        $path = $this->getUploadRootDirNombreArchivo() . $carpeta . $archivo_fotografia;
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
        } else {
            return $this->getNoLogoImage();
        }
    }

    #######################################################################################
    ############# FUNCIONES PARA MOSTRAR A USUARIO ########################################
    public function getFotografiaMW() {
        return null === $this->nombreArchivo ? null : $this->getPathArchivo('/xl_wm/' ,$this->nombreArchivo);
    }
    public function getFotografiaXL() {
        return null === $this->nombreArchivo ? null : $this->getPathArchivo('/xl/' ,$this->nombreArchivo);
    }
    public function getFotografiaL() {
        return null === $this->nombreArchivo ? null : $this->getPathArchivo('/l/' ,$this->nombreArchivo);
    }
    public function getFotografiaM() {
        return null === $this->nombreArchivo ? null : $this->getPathArchivo('/m/' ,$this->nombreArchivo);
    }
    public function getFotografiaS() {
        return null === $this->nombreArchivo ? null : $this->getPathArchivo('/m/' ,$this->nombreArchivo);
    }

    #######################################################################################
    ################## RUTAS DE IMAGENES ##################################################
    public function rutaFotografiaMW() {
        return  $this->getUploadRootDirNombreArchivo().'/xl_wm/'.$this->nombreArchivo;
    }
    public function rutaFotografiaXL() {
        return  $this->getUploadRootDirNombreArchivo().'/xl/'.$this->nombreArchivo;
    }
    public function rutaFotografiaL() {
        return  $this->getUploadRootDirNombreArchivo().'/l/'.$this->nombreArchivo;
    }
    public function rutaFotografiaM() {
        return  $this->getUploadRootDirNombreArchivo().'/m/'.$this->nombreArchivo;
    }
    public function rutaFotografiaS() {
        return  $this->getUploadRootDirNombreArchivo().'/s/'.$this->nombreArchivo;
    }
    public function rutaFotografiaOriginal() {
        return  $this->getUploadRootDirNombreArchivo().'/'.$this->nombreArchivo;
    }

    /**
     * Regresa una imagen por defecto cuando no encuentra la original en base 64
     */
    public function getNoLogoImage() {
        $archivo = $this->getUploadRootDirNombreArchivo() . '/noImage.png';
        $type = pathinfo($archivo, PATHINFO_EXTENSION);
        $data = file_get_contents($archivo);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
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

    /**
    * "funcion para eliminar las imagenes en las carpetas mini_thumb, thumb y original
    * de un articulo."
    *
    * @ORM\PostRemove()
    */
    public function eliminarFotografiaArticulo(){
        /* imagen original */
        $nombreArchivoOriginal = $this->rutaFotografiaOriginal();
        if(file_exists($nombreArchivoOriginal)){
            unlink($nombreArchivoOriginal);
        }
         /* marca de agua */
        $nombreArchivoMW = $this->rutaFotografiaMW();
        if(file_exists($nombreArchivoMW)){
            unlink($nombreArchivoMW);
        }
         /* imagen XL */
         $nombreArchivoXL = $this->rutaFotografiaXL();
        if(file_exists($nombreArchivoXL)){
            unlink($nombreArchivoXL);
        }
        /* imagen L */
        $nombreArchivoL = $this->rutaFotografiaL();
        if(file_exists($nombreArchivoL)){
            unlink($nombreArchivoL);
        }
        /* imagen M */
        $nombreArchivoM = $this->rutaFotografiaM();
        if(file_exists($nombreArchivoM)){
            unlink($nombreArchivoM);
        }
        /* imagen S */
        $nombreArchivoS = $this->rutaFotografiaS();
        if(file_exists($nombreArchivoS)){
            unlink($nombreArchivoS);
        }
    }
}

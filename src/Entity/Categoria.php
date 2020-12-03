<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Categoria")
 * @ORM\Entity(repositoryClass=CategoriaRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Categoria
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="nombre", type="string", length=50)
     */
    private $nombre;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_creo_id", referencedColumnName="id")
     * })
     */
    private $usuarioCreador;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_edito_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $usuarioEditor;

    /**
     * @ORM\Column(name="fecha_hora_creacion", type="datetime")
     */
    private $fechahoraCreacion;

    /**
     * @ORM\Column(name="fecha_hora_edicion", type="datetime", nullable=true)
     */
    private $fechahoraEdicion;

    /**
     * @ORM\OneToMany(targetEntity=Articulo::class, mappedBy="categoria")
     */
    private $articulos;

    public function __construct()
    {
        $this->fechahoraCreacion = new \DateTime();
        $this->articulos = new ArrayCollection();
    }

    public function __toString() {
        return $this->nombre;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function atUpdate() {

        if(is_null($this->fechahoraCreacion)){
            $this->fechahoraCreacion = new \DateTime();
        }

        $this->setFechahoraEdicion(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return \User
     */
    public function getUsuarioCreador()
    {
        return $this->usuarioCreador;
    }

    /**
     * @param \User $usuarioCreador
     */
    public function setUsuarioCreador($usuarioCreador): void
    {
        $this->usuarioCreador = $usuarioCreador;
    }

    /**
     * @return \User
     */
    public function getUsuarioEditor()
    {
        return $this->usuarioEditor;
    }

    /**
     * @param \User $usuarioEditor
     */
    public function setUsuarioEditor($usuarioEditor): void
    {
        $this->usuarioEditor = $usuarioEditor;
    }

    /**
     * @return \DateTime
     */
    public function getFechahoraCreacion(): \DateTime
    {
        return $this->fechahoraCreacion;
    }

    /**
     * @param \DateTime $fechahoraCreacion
     */
    public function setFechahoraCreacion(\DateTime $fechahoraCreacion): void
    {
        $this->fechahoraCreacion = $fechahoraCreacion;
    }

    /**
     * @return mixed
     */
    public function getFechahoraEdicion()
    {
        return $this->fechahoraEdicion;
    }

    /**
     * @param mixed $fechahoraEdicion
     */
    public function setFechahoraEdicion($fechahoraEdicion): void
    {
        $this->fechahoraEdicion = $fechahoraEdicion;
    }

    /**
     * @return Collection|Articulo[]
     */
    public function getArticulos(): Collection
    {
        return $this->articulos;
    }

    public function addArticulo(Articulo $articulo): self
    {
        if (!$this->articulos->contains($articulo)) {
            $this->articulos[] = $articulo;
            $articulo->setCategoria($this);
        }

        return $this;
    }

    public function removeArticulo(Articulo $articulo): self
    {
        if ($this->articulos->contains($articulo)) {
            $this->articulos->removeElement($articulo);
            // set the owning side to null (unless already changed)
            if ($articulo->getCategoria() === $this) {
                $articulo->setCategoria(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\DocumentoRegistroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="DocumentoRegistro")
 * @ORM\Entity(repositoryClass=DocumentoRegistroRepository::class)
 */
class DocumentoRegistro
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="fecha_hora_creacion", type="datetime")
     */
    private $fechaHoraCreacion;

    /**
     * @ORM\Column(name="cantidad", type="integer")
     */
    private $cantidad;

    /**
     * @ORM\Column(name="precio", type="decimal", precision=10, scale=2)
     */
    private $precio;

    /**
     * @ORM\Column(name="total", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity=Articulo::class, inversedBy="documentoRegistros")
     * @ORM\JoinColumn(name="articulo_id", nullable=false)
     */
    private $articulo;

    /**
     * @ORM\ManyToOne(targetEntity=Documento::class, inversedBy="registros")
     * @ORM\JoinColumn(name="documento_id", nullable=false)
     */
    private $Documento;

    /**
     * @ORM\Column(name="descuento", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $descuento;

    /**
     * @ORM\Column(name="promocion", type="boolean", nullable=true)
     */
    private $promocion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaHoraCreacion(): ?\DateTimeInterface
    {
        return $this->fechaHoraCreacion;
    }

    public function setFechaHoraCreacion(\DateTimeInterface $fechaHoraCreacion): self
    {
        $this->fechaHoraCreacion = $fechaHoraCreacion;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(string $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(?string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getArticulo(): ?articulo
    {
        return $this->articulo;
    }

    public function setArticulo(?articulo $articulo): self
    {
        $this->articulo = $articulo;

        return $this;
    }

    public function getDocumento(): ?Documento
    {
        return $this->Documento;
    }

    public function setDocumento(?Documento $Documento): self
    {
        $this->Documento = $Documento;

        return $this;
    }

    public function getDescuento(): ?string
    {
        return $this->descuento;
    }

    public function setDescuento(?string $descuento): self
    {
        $this->descuento = $descuento;

        return $this;
    }

    public function getPromocion(): ?bool
    {
        return $this->promocion;
    }

    public function setPromocion(?bool $promocion): self
    {
        $this->promocion = $promocion;

        return $this;
    }
}

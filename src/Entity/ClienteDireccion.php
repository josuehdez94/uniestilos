<?php

namespace App\Entity;

use App\Repository\ClienteDireccionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cliente_direccion")
 * @ORM\Entity(repositoryClass=ClienteDireccionRepository::class)
 */
class ClienteDireccion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="nombre_completo", type="string", length=255)
     */
    private $nombreCompleto;

    /**
     * @ORM\Column(name="calle", type="string", length=100)
     */
    private $calle;

    /**
     * @ORM\Column(name="numero_exterior", type="integer")
     */
    private $numeroExterior;

    /**
     * @ORM\Column(name="numero_interior", type="integer", nullable=true)
     */
    private $numeroInterior;

    /**
     * @ORM\ManyToOne(targetEntity=Municipio::class, inversedBy="clienteDireccion")
     * @ORM\JoinColumn(name="municipio_id", nullable=false)
     */
    private $municipio;

    /**
     * @ORM\Column(name="codigo_postal", type="integer")
     */
    private $codigoPostal;

    /**
     * @ORM\Column(name="telefono", type="bigint")
     */
    private $telefono;

    /**
     * @ORM\Column(name="instrucciones_entrega", type="string", length=255, nullable=true)
     */
    private $instrucionesEntrega;

    /**
     * @ORM\Column(name="referencias", type="string", length=255, nullable=true)
     */
    private $Referencias;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="clienteDirecciones")
     * @ORM\JoinColumn(name="cliente_id", nullable=false)
     */
    private $cliente;

    /**
     * @ORM\Column(name="colonia", type="string", length=100)
     */
    private $colonia;

    public function __construct()
    {
        //$this->municipio = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreCompleto(): ?string
    {
        return $this->nombreCompleto;
    }

    public function setNombreCompleto(string $nombreCompleto): self
    {
        $this->nombreCompleto = $nombreCompleto;

        return $this;
    }

    public function getCalle(): ?string
    {
        return $this->calle;
    }

    public function setCalle(string $calle): self
    {
        $this->calle = $calle;

        return $this;
    }

    public function getNumeroExterior(): ?int
    {
        return $this->numeroExterior;
    }

    public function setNumeroExterior(int $numeroExterior): self
    {
        $this->numeroExterior = $numeroExterior;

        return $this;
    }

    public function getNumeroInterior(): ?int
    {
        return $this->numeroInterior;
    }

    public function setNumeroInterior(?int $numeroInterior): self
    {
        $this->numeroInterior = $numeroInterior;

        return $this;
    }

    /**
     * @return Collection|Municipio[]
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }
    
    public function setMunicipio($municipio): self
    {
         $this->municipio = $municipio;

        return $this;
    }

    /*public function addMunicipio(Municipio $municipio): self
    {
        if (!$this->municipio->contains($municipio)) {
            $this->municipio[] = $municipio;
            $municipio->setClienteDireccion($this);
        }

        return $this;
    }

    public function removeMunicipio(Municipio $municipio): self
    {
        if ($this->municipio->removeElement($municipio)) {
            // set the owning side to null (unless already changed)
            if ($municipio->getClienteDireccion() === $this) {
                $municipio->setClienteDireccion(null);
            }
        }

        return $this;
    }*/

    public function getCodigoPostal(): ?int
    {
        return $this->codigoPostal;
    }

    public function setCodigoPostal(int $codigoPostal): self
    {
        $this->codigoPostal = $codigoPostal;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->telefono;
    }

    public function setTelefono(int $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getInstrucionesEntrega(): ?string
    {
        return $this->instrucionesEntrega;
    }

    public function setInstrucionesEntrega(?string $instrucionesEntrega): self
    {
        $this->instrucionesEntrega = $instrucionesEntrega;

        return $this;
    }

    public function getReferencias(): ?string
    {
        return $this->Referencias;
    }

    public function setReferencias(?string $Referencias): self
    {
        $this->Referencias = $Referencias;

        return $this;
    }

    public function getCliente(): ?User
    {
        return $this->cliente;
    }

    public function setCliente($cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getColonia(): ?string
    {
        return $this->colonia;
    }

    public function setColonia(string $colonia): self
    {
        $this->colonia = $colonia;

        return $this;
    }
}

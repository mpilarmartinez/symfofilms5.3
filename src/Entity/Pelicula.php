<?php

namespace App\Entity;

use App\Repository\PeliculaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PeliculaRepository::class)]
class Pelicula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 128)]
    private $titulo;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private $director;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $duracion;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private $genero;

    #[ORM\Column(type: 'text', nullable: true)]
    private $sinopsis;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $estreno;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $valoracion;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $caratula;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'peliculas')]
    private $user;

    #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'peliculas')]
    private $actores;

    public function __construct()
    {
        $this->actores = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getDuracion(): ?int
    {
        return $this->duracion;
    }

    public function setDuracion(?int $duracion): self
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function setGenero(?string $genero): self
    {
        $this->genero = $genero;

        return $this;
    }
    

    public function __toString()
    {
        return "$this->titulo ($this->duracion m), de $this->director. Genero $this->genero";
    }

    public function getSinopsis(): ?string
    {
        return $this->sinopsis;
    }

    public function setSinopsis(?string $sinopsis): self
    {
        $this->sinopsis = $sinopsis;

        return $this;
    }

    public function getEstreno(): ?int
    {
        return $this->estreno;
    }

    public function setEstreno(?int $estreno): self
    {
        $this->estreno = $estreno;

        return $this;
    }

    public function getValoracion(): ?int
    {
        return $this->valoracion;
    }

    public function setValoracion(?int $valoracion): self
    {
        $this->valoracion = $valoracion;

        return $this;
    }

    public function getCaratula(): ?string
    {
        return $this->caratula;
    }

    public function setCaratula(?string $Caratula): self
    {
        $this->caratula = $Caratula;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActores(): Collection
    {
        return $this->actores;
    }

    public function addActore(Actor $actore): self
    {
        if (!$this->actores->contains($actore)) {
            $this->actores[] = $actore;
        }

        return $this;
    }

    public function removeActore(Actor $actore): self
    {
        $this->actores->removeElement($actore);

        return $this;
    }

}

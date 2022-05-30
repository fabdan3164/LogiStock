<?php

namespace App\Entity;

use App\Repository\ReceptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReceptionRepository::class)]
class Reception
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $bonDeCommande;

    #[ORM\OneToMany(mappedBy: 'idReception', targetEntity: Conteneur::class)]
    private $conteneurs;

    public function __construct()
    {
        $this->conteneurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBonDeCommande(): ?int
    {
        return $this->bonDeCommande;
    }

    public function setBonDeCommande(int $bonDeCommande): self
    {
        $this->bonDeCommande = $bonDeCommande;

        return $this;
    }


    /**
     * @return Collection<int, Conteneur>
     */
    public function getConteneurs(): Collection
    {
        return $this->conteneurs;
    }

    public function addConteneur(Conteneur $conteneur): self
    {
        if (!$this->conteneurs->contains($conteneur)) {
            $this->conteneurs[] = $conteneur;
            $conteneur->setIdReception($this);
        }

        return $this;
    }

    public function removeConteneur(Conteneur $conteneur): self
    {
        if ($this->conteneurs->removeElement($conteneur)) {
            // set the owning side to null (unless already changed)
            if ($conteneur->getIdReception() === $this) {
                $conteneur->setIdReception(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->bonDeCommande;
    }





}

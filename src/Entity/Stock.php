<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $adresseStock;

    #[ORM\Column(type: 'boolean')]
    private $multiStockage;

    #[ORM\OneToMany(mappedBy: 'idStock', targetEntity: Conteneur::class)]
    private $conteneurs;

    public function __construct()
    {
        $this->conteneurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseStock(): ?string
    {
        return $this->adresseStock;
    }

    public function setAdresseStock(string $adresseStock): self
    {
        $this->adresseStock = $adresseStock;

        return $this;
    }

    public function isMultiStockage(): ?bool
    {
        return $this->multiStockage;
    }

    public function setMultiStockage(bool $multiStockage): self
    {
        $this->multiStockage = $multiStockage;

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
            $conteneur->setIdStock($this);
        }

        return $this;
    }

    public function removeConteneur(Conteneur $conteneur): self
    {
        if ($this->conteneurs->removeElement($conteneur)) {
            // set the owning side to null (unless already changed)
            if ($conteneur->getIdStock() === $this) {
                $conteneur->setIdStock(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->adresseStock;
    }
}

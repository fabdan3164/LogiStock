<?php

namespace App\Entity;

use App\Repository\FluxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxRepository::class)]
class Flux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\Column(type: 'integer')]
    private $quantite;

    #[ORM\Column(type: 'datetime')]
    private $dateFlux;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $origine;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $adresseStock;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $codeConteneur;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $partNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDateFlux(): ?\DateTimeInterface
    {
        return $this->dateFlux;
    }

    public function setDateFlux(\DateTimeInterface $dateFlux): self
    {
        $this->dateFlux = $dateFlux;

        return $this;
    }

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(?string $origine): self
    {
        $this->origine = $origine;

        return $this;
    }

    public function getAdresseStock(): ?string
    {
        return $this->adresseStock;
    }

    public function setAdresseStock(?string $adresseStock): self
    {
        $this->adresseStock = $adresseStock;

        return $this;
    }

    public function getCodeConteneur(): ?string
    {
        return $this->codeConteneur;
    }

    public function setCodeConteneur(?string $codeConteneur): self
    {
        $this->codeConteneur = $codeConteneur;

        return $this;
    }

    public function getPartNumber(): ?string
    {
        return $this->partNumber;
    }

    public function setPartNumber(?string $partNumber): self
    {
        $this->partNumber = $partNumber;

        return $this;
    }


}

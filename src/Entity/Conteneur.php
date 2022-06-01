<?php

namespace App\Entity;

use App\Repository\ConteneurRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: ConteneurRepository::class)]
class Conteneur implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $codeConteneur;

    #[ORM\Column(type: 'integer')]
    private $quantite;

    #[ORM\Column(type: 'datetime')]
    private $dateReception;


    #[ORM\ManyToOne(targetEntity: Reception::class, inversedBy: 'conteneurs')]
    #[ORM\JoinColumn(nullable: false)]
    private $idReception;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'conteneurs')]
    #[ORM\JoinColumn(nullable: false)]
    private $idProduit;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'conteneurs')]
    #[ORM\JoinColumn(nullable: false)]
    private $idStock;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeConteneur(): ?string
    {
        return $this->codeConteneur;
    }

    public function setCodeConteneur(string $codeConteneur): self
    {
        $this->codeConteneur = $codeConteneur;

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

    public function getDateReception(): ?\DateTimeInterface
    {
        return $this->dateReception;
    }

    public function setDateReception(\DateTimeInterface $dateReception): self
    {
        $this->dateReception = $dateReception;

        return $this;
    }

    public function getIdReception(): ?Reception
    {
        return $this->idReception;
    }

    public function setIdReception(?Reception $idReception): self
    {
        $this->idReception = $idReception;

        return $this;
    }

    public function getIdProduit(): ?Produit
    {
        return $this->idProduit;
    }

    public function setIdProduit(?Produit $idProduit): self
    {
        $this->idProduit = $idProduit;

        return $this;
    }

    public function getIdStock(): ?Stock
    {
        return $this->idStock;
    }

    public function setIdStock(?Stock $idStock): self
    {
        $this->idStock = $idStock;

        return $this;
    }

    public function __toString(): string
    {
        return $this->codeConteneur;
    }

    public function jsonSerialize()
    {
        return array(
            'codeConteneur' => $this->codeConteneur,
            'idConteneur' => $this->id,
        );
    }



}

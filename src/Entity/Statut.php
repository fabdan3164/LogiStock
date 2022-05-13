<?php

namespace App\Entity;

use App\Repository\StatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $statutDenomination;

    #[ORM\OneToMany(mappedBy: 'idStatut', targetEntity: Ligne::class)]
    private $lignes;

    #[ORM\OneToMany(mappedBy: 'idStatut', targetEntity: Commande::class)]
    private $commandes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatutDenomination(): ?string
    {
        return $this->statutDenomination;
    }

    public function setStatutDenomination(string $statutDenomination): self
    {
        $this->statutDenomination = $statutDenomination;

        return $this;
    }

    /**
     * @return Collection<int, Ligne>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(Ligne $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes[] = $ligne;
            $ligne->setIdStatut($this);
        }

        return $this;
    }

    public function removeLigne(Ligne $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            // set the owning side to null (unless already changed)
            if ($ligne->getIdStatut() === $this) {
                $ligne->setIdStatut(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->setIdStatut($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getIdStatut() === $this) {
                $commande->setIdStatut(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->statutDenomination;
    }
}

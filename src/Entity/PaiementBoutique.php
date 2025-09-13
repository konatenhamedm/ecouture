<?php

namespace App\Entity;

use App\Repository\PaiementBoutiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaiementBoutiqueRepository::class)]
class PaiementBoutique extends Paiement
{
   

    #[ORM\ManyToOne(inversedBy: 'paiementBoutiques')]
    private ?Boutique $boutique = null;

    #[ORM\Column(nullable: true)]
     #[Groups(["group1", "group_type"])]
    private ?int $quantite = null;

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function setBoutique(?Boutique $boutique): static
    {
        $this->boutique = $boutique;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }
}

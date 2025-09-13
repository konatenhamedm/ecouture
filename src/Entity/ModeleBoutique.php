<?php

namespace App\Entity;

use App\Repository\ModeleBoutiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModeleBoutiqueRepository::class)]
#[UniqueEntity(fields: ['boutique', 'modele'], message: 'Cet modele est deja ajoute au boutique.', errorPath: 'modele')]
class ModeleBoutique
{
    use TraitEntity;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(["group1"])]
    private ?int $id = null;

    #[ORM\Column]
     #[Groups(["group1"])]
    private ?int $quantite = null;

    #[ORM\Column(length: 255)]
     #[Groups(["group1"])]
    private ?string $prix = null;

    #[ORM\ManyToOne(inversedBy: 'modeleBoutiques')]
     #[Groups(["group1"])]
    private ?Modele $modele = null;

    #[ORM\ManyToOne(inversedBy: 'modeleBoutiques')]
    private ?Boutique $boutique = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getModele(): ?Modele
    {
        return $this->modele;
    }

    public function setModele(?Modele $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function setBoutique(?Boutique $boutique): static
    {
        $this->boutique = $boutique;

        return $this;
    }
}

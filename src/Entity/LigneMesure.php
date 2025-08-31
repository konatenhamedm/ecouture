<?php

namespace App\Entity;

use App\Repository\LigneMesureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LigneMesureRepository::class)]
class LigneMesure
{ use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(["group1", "group_type"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
     #[Groups(["group1", "group_type"])]
    private ?string $taille = null;

    #[ORM\ManyToOne(inversedBy: 'ligneMesures')]
     #[Groups(["group1", "group_type"])]
    private ?CategorieMesure $categorieMesure = null;

    #[ORM\ManyToOne(inversedBy: 'ligneMesures')]
    private ?Mesure $mesure = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getCategorieMesure(): ?CategorieMesure
    {
        return $this->categorieMesure;
    }

    public function setCategorieMesure(?CategorieMesure $categorieMesure): static
    {
        $this->categorieMesure = $categorieMesure;

        return $this;
    }

    public function getMesure(): ?Mesure
    {
        return $this->mesure;
    }

    public function setMesure(?Mesure $mesure): static
    {
        $this->mesure = $mesure;

        return $this;
    }
}

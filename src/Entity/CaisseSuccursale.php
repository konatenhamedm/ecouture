<?php

namespace App\Entity;

use App\Repository\CaisseSuccursaleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseSuccursaleRepository::class)]
class CaisseSuccursale extends Caisse
{

   
    #[ORM\ManyToOne(inversedBy: 'caisseSuccursales')]
    private ?Surccursale $succursale = null;

    public function getSuccursale(): ?Surccursale
    {
        return $this->succursale;
    }

    public function setSuccursale(?Surccursale $succursale): static
    {
        $this->succursale = $succursale;

        return $this;
    }
}

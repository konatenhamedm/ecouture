<?php

namespace App\Entity;

use App\Repository\PaiementAbonnementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementAbonnementRepository::class)]
class PaiementAbonnement extends Paiement
{
    #[ORM\ManyToOne(inversedBy: 'paiementAbonnements')]
    private ?ModuleAbonnement $moduleAbonnement = null;

    public function getModuleAbonnement(): ?ModuleAbonnement
    {
        return $this->moduleAbonnement;
    }

    public function setModuleAbonnement(?ModuleAbonnement $moduleAbonnement): static
    {
        $this->moduleAbonnement = $moduleAbonnement;

        return $this;
    }
}

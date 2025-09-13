<?php

namespace App\Entity;

use App\Repository\PaiementAbonnementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementAbonnementRepository::class)]
class PaiementAbonnement extends Paiement
{
    #[ORM\ManyToOne(inversedBy: 'paiementAbonnements')]
    private ?ModuleAbonnement $moduleAbonnement = null;

    #[ORM\ManyToOne(inversedBy: 'paiementAbonnements')]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(length: 255)]
    private ?string $channel = null;

    #[ORM\Column]
    private ?int $state = null;

    #[ORM\Column(length: 255)]
    private ?string $pays = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $dataUser = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $dataSuccursale = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $dataBoutique = null;

    public function getModuleAbonnement(): ?ModuleAbonnement
    {
        return $this->moduleAbonnement;
    }

    public function setModuleAbonnement(?ModuleAbonnement $moduleAbonnement): static
    {
        $this->moduleAbonnement = $moduleAbonnement;

        return $this;
    }



    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getDataUser(): ?array
    {
        return $this->dataUser;
    }

    public function setDataUser(?array $dataUser): static
    {
        $this->dataUser = $dataUser;

        return $this;
    }

    public function getDataSuccursale(): ?array
    {
        return $this->dataSuccursale;
    }

    public function setDataSuccursale(?array $dataSuccursale): static
    {
        $this->dataSuccursale = $dataSuccursale;

        return $this;
    }

    public function getDataBoutique(): ?array
    {
        return $this->dataBoutique;
    }

    public function setDataBoutique(?array $dataBoutique): static
    {
        $this->dataBoutique = $dataBoutique;

        return $this;
    }
}

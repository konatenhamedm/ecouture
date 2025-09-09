<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(["group1", "group_type"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'settings')]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
     #[Groups(["group1", "group_type"])]
    private ?int $nombreUser = null;

    #[ORM\Column(nullable: true)]
     #[Groups(["group1", "group_type"])]
    private ?int $nombreSms = null;

    #[ORM\Column]
     #[Groups(["group1", "group_type"])]
    private ?int $nombreSuccursale = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sendMesssageAutomaticIfRendezVousProche = true;

    #[ORM\Column(nullable: true)]
     #[Groups(["group1", "group_type"])]
    private ?int $nombreJourRestantPourEnvoyerSms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
     #[Groups(["group1", "group_type"])]
    private ?string $modeleMessageEnvoyerPourRendezVousProche = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombreUser(): ?int
    {
        return $this->nombreUser;
    }

    public function setNombreUser(?int $nombreUser): static
    {
        $this->nombreUser = $nombreUser;

        return $this;
    }

    public function getNombreSms(): ?int
    {
        return $this->nombreSms;
    }

    public function setNombreSms(?int $nombreSms): static
    {
        $this->nombreSms = $nombreSms;

        return $this;
    }

    public function getNombreSuccursale(): ?int
    {
        return $this->nombreSuccursale;
    }

    public function setNombreSuccursale(int $nombreSuccursale): static
    {
        $this->nombreSuccursale = $nombreSuccursale;

        return $this;
    }

    public function isSendMesssageAutomaticIfRendezVousProche(): ?bool
    {
        return $this->sendMesssageAutomaticIfRendezVousProche;
    }

    public function setSendMesssageAutomaticIfRendezVousProche(?bool $sendMesssageAutomaticIfRendezVousProche): static
    {
        $this->sendMesssageAutomaticIfRendezVousProche = $sendMesssageAutomaticIfRendezVousProche;

        return $this;
    }

    public function getNombreJourRestantPourEnvoyerSms(): ?int
    {
        return $this->nombreJourRestantPourEnvoyerSms;
    }

    public function setNombreJourRestantPourEnvoyerSms(?int $nombreJourRestantPourEnvoyerSms): static
    {
        $this->nombreJourRestantPourEnvoyerSms = $nombreJourRestantPourEnvoyerSms;

        return $this;
    }

    public function getModeleMessageEnvoyerPourRendezVousProche(): ?string
    {
        return $this->modeleMessageEnvoyerPourRendezVousProche;
    }

    public function setModeleMessageEnvoyerPourRendezVousProche(?string $modeleMessageEnvoyerPourRendezVousProche): static
    {
        $this->modeleMessageEnvoyerPourRendezVousProche = $modeleMessageEnvoyerPourRendezVousProche;

        return $this;
    }
}

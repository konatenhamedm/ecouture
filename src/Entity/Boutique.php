<?php

namespace App\Entity;

use App\Repository\BoutiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoutiqueRepository::class)]
class Boutique
{

    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $contact = null;

    #[ORM\Column(length: 255)]
    private ?string $situation = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'boutiques')]
    private ?Entreprise $entreprise = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'boutique')]
    private Collection $users;

    /**
     * @var Collection<int, ModeleBoutique>
     */
    #[ORM\OneToMany(targetEntity: ModeleBoutique::class, mappedBy: 'boutique')]
    private Collection $modeleBoutiques;

    /**
     * @var Collection<int, PaiementBoutique>
     */
    #[ORM\OneToMany(targetEntity: PaiementBoutique::class, mappedBy: 'boutique')]
    private Collection $paiementBoutiques;

    /**
     * @var Collection<int, CaisseBoutique>
     */
    #[ORM\OneToMany(targetEntity: CaisseBoutique::class, mappedBy: 'boutique')]
    private Collection $caisseBoutiques;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'boutique')]
    private Collection $reservations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->modeleBoutiques = new ArrayCollection();
        $this->paiementBoutiques = new ArrayCollection();
        $this->caisseBoutiques = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getSituation(): ?string
    {
        return $this->situation;
    }

    public function setSituation(string $situation): static
    {
        $this->situation = $situation;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setBoutique($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getBoutique() === $this) {
                $user->setBoutique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ModeleBoutique>
     */
    public function getModeleBoutiques(): Collection
    {
        return $this->modeleBoutiques;
    }

    public function addModeleBoutique(ModeleBoutique $modeleBoutique): static
    {
        if (!$this->modeleBoutiques->contains($modeleBoutique)) {
            $this->modeleBoutiques->add($modeleBoutique);
            $modeleBoutique->setBoutique($this);
        }

        return $this;
    }

    public function removeModeleBoutique(ModeleBoutique $modeleBoutique): static
    {
        if ($this->modeleBoutiques->removeElement($modeleBoutique)) {
            // set the owning side to null (unless already changed)
            if ($modeleBoutique->getBoutique() === $this) {
                $modeleBoutique->setBoutique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementBoutique>
     */
    public function getPaiementBoutiques(): Collection
    {
        return $this->paiementBoutiques;
    }

    public function addPaiementBoutique(PaiementBoutique $paiementBoutique): static
    {
        if (!$this->paiementBoutiques->contains($paiementBoutique)) {
            $this->paiementBoutiques->add($paiementBoutique);
            $paiementBoutique->setBoutique($this);
        }

        return $this;
    }

    public function removePaiementBoutique(PaiementBoutique $paiementBoutique): static
    {
        if ($this->paiementBoutiques->removeElement($paiementBoutique)) {
            // set the owning side to null (unless already changed)
            if ($paiementBoutique->getBoutique() === $this) {
                $paiementBoutique->setBoutique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CaisseBoutique>
     */
    public function getCaisseBoutiques(): Collection
    {
        return $this->caisseBoutiques;
    }

    public function addCaisseBoutique(CaisseBoutique $caisseBoutique): static
    {
        if (!$this->caisseBoutiques->contains($caisseBoutique)) {
            $this->caisseBoutiques->add($caisseBoutique);
            $caisseBoutique->setBoutique($this);
        }

        return $this;
    }

    public function removeCaisseBoutique(CaisseBoutique $caisseBoutique): static
    {
        if ($this->caisseBoutiques->removeElement($caisseBoutique)) {
            // set the owning side to null (unless already changed)
            if ($caisseBoutique->getBoutique() === $this) {
                $caisseBoutique->setBoutique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setBoutique($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getBoutique() === $this) {
                $reservation->setBoutique(null);
            }
        }

        return $this;
    }
}

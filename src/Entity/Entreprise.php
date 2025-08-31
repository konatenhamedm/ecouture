<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(["group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
     #[Groups(["group1"])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
     #[Groups(["group1"])]
    private ?string $numero = null;


    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group1"])]
    private ?Fichier $logo = null;


    #[ORM\Column(length: 255, nullable: true)]
     #[Groups(["group1"])]
    private ?string $email = null;

    /**
     * @var Collection<int, CategorieMesure>
     */
    #[ORM\OneToMany(targetEntity: CategorieMesure::class, mappedBy: 'entreprise')]
    private Collection $categorieMesures;

    

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'entreprise')]
    private Collection $notifications;

    /**
     * @var Collection<int, Abonnement>
     */
    #[ORM\OneToMany(targetEntity: Abonnement::class, mappedBy: 'entreprise')]
    private Collection $abonnements;

    /**
     * @var Collection<int, TypeMesure>
     */
    #[ORM\OneToMany(targetEntity: TypeMesure::class, mappedBy: 'entreprise')]
    private Collection $typeMesures;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'entreprise')]
    private Collection $users;

    /**
     * @var Collection<int, Surccursale>
     */
    #[ORM\OneToMany(targetEntity: Surccursale::class, mappedBy: 'entreprise')]
    private Collection $surccursales;

    public function __construct()
    {
        $this->categorieMesures = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->abonnements = new ArrayCollection();
        $this->typeMesures = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->surccursales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getLogo(): ?Fichier
    {
        return $this->logo;
    }

    public function setLogo(?Fichier $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, CategorieMesure>
     */
    public function getCategorieMesures(): Collection
    {
        return $this->categorieMesures;
    }

    public function addCategorieMesure(CategorieMesure $categorieMesure): static
    {
        if (!$this->categorieMesures->contains($categorieMesure)) {
            $this->categorieMesures->add($categorieMesure);
            $categorieMesure->setEntreprise($this);
        }

        return $this;
    }

    public function removeCategorieMesure(CategorieMesure $categorieMesure): static
    {
        if ($this->categorieMesures->removeElement($categorieMesure)) {
            // set the owning side to null (unless already changed)
            if ($categorieMesure->getEntreprise() === $this) {
                $categorieMesure->setEntreprise(null);
            }
        }

        return $this;
    }

    

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setEntreprise($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getEntreprise() === $this) {
                $notification->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Abonnement>
     */
    public function getAbonnements(): Collection
    {
        return $this->abonnements;
    }

    public function addAbonnement(Abonnement $abonnement): static
    {
        if (!$this->abonnements->contains($abonnement)) {
            $this->abonnements->add($abonnement);
            $abonnement->setEntreprise($this);
        }

        return $this;
    }

    public function removeAbonnement(Abonnement $abonnement): static
    {
        if ($this->abonnements->removeElement($abonnement)) {
            // set the owning side to null (unless already changed)
            if ($abonnement->getEntreprise() === $this) {
                $abonnement->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeMesure>
     */
    public function getTypeMesures(): Collection
    {
        return $this->typeMesures;
    }

    public function addTypeMesure(TypeMesure $typeMesure): static
    {
        if (!$this->typeMesures->contains($typeMesure)) {
            $this->typeMesures->add($typeMesure);
            $typeMesure->setEntreprise($this);
        }

        return $this;
    }

    public function removeTypeMesure(TypeMesure $typeMesure): static
    {
        if ($this->typeMesures->removeElement($typeMesure)) {
            // set the owning side to null (unless already changed)
            if ($typeMesure->getEntreprise() === $this) {
                $typeMesure->setEntreprise(null);
            }
        }

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
            $user->setEntreprise($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getEntreprise() === $this) {
                $user->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Surccursale>
     */
    public function getSurccursales(): Collection
    {
        return $this->surccursales;
    }

    public function addSurccursale(Surccursale $surccursale): static
    {
        if (!$this->surccursales->contains($surccursale)) {
            $this->surccursales->add($surccursale);
            $surccursale->setEntreprise($this);
        }

        return $this;
    }

    public function removeSurccursale(Surccursale $surccursale): static
    {
        if ($this->surccursales->removeElement($surccursale)) {
            // set the owning side to null (unless already changed)
            if ($surccursale->getEntreprise() === $this) {
                $surccursale->setEntreprise(null);
            }
        }

        return $this;
    }
}

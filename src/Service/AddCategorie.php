<?php



namespace App\Service;

use App\Entity\CategorieMesure;
use App\Entity\CategorieTypeMesure;
use App\Entity\Entreprise;
use App\Entity\LigneModule;
use App\Entity\Setting;
use App\Entity\TypeMesure;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AddCategorie
{


    public function __construct(private EntityManagerInterface $entityManager) {}

    public function setParametreForEntreprise(User $user): void
    {
        $entreprise = $user->getEntreprise();


        $typeLibelles = ['veste', 'pantalon'];
        $types = [];

        foreach ($typeLibelles as $libelle) {
            $type = new TypeMesure();
            $type->setLibelle($libelle);
            $type->setEntreprise($entreprise);
            $type->setCreatedAtValue(new \DateTime());
            $type->setCreatedBy($user);
            $this->entityManager->persist($type);
            $types[$libelle] = $type;
        }

        $categorieLibelles = ['largeur', 'longueur', 'ceinture'];
        $categories = [];

        foreach ($categorieLibelles as $libelle) {
            $categorie = new CategorieMesure();
            $categorie->setLibelle($libelle);
            $categorie->setEntreprise($entreprise);
            $categorie->setCreatedAtValue(new \DateTime());
            $categorie->setCreatedBy($user);
            $this->entityManager->persist($categorie);
            $categories[$libelle] = $categorie;
        }

        $categorieType = new CategorieTypeMesure();
        $categorieType->setTypeMesure($types['veste']);
        $categorieType->setCreatedAtValue(new \DateTime());
        $categorieType->setCreatedBy($user);
        $categorieType->setCategorieMesure($categories['largeur']);
        $this->entityManager->persist($categorieType);

        $this->entityManager->flush();
    }


    public function setting(Entreprise $entreprise,$data = []): void
    {

       
        $setting = new Setting();
        $setting->setEntreprise($entreprise);
        $setting->setNombreSms($data['sms']);
        $setting->setNombreSuccursale($data['succursale']);
        $setting->setNombreUser($data['user']);
        $setting->setNombreJourRestantPourEnvoyerSms(10);
        $setting->setModeleMessageEnvoyerPourRendezVousProche("Bonjour, ceci est un rappel pour votre rendez-vous prévu prochainement dans 10 jours, merci de vous présenter à l’heure.");
        $setting->isSendMesssageAutomaticIfRendezVousProche(false);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    
}

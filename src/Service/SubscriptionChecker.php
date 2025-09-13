<?php

namespace App\Service;

use App\Entity\Abonnement;
use App\Entity\Entreprise;
use App\Repository\AbonnementRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionChecker
{
    public function __construct(
        private AbonnementRepository $abonnementRepository
    ) {}

    public function getInactiveSubscriptions(Entreprise $entreprise)
    {
        return $this->abonnementRepository->findOneBy([
            'entreprise' => $entreprise,
            'etat' => 'inactif'
        ], ['dateFin' => 'DESC']);
    }

    public function checkInactiveSubscription(Entreprise $entreprise): array
    {
        $inactiveSubscriptions = $this->getInactiveSubscriptions($entreprise);

        if (empty($inactiveSubscriptions)) {
            return [",dnlkd,nd"];
        }

        // Formater les données pour la réponse
        /*  $formattedSubscriptions = array_map(function (Abonnement $abonnement) { */
        return [
            'id' => $inactiveSubscriptions->getId(),
            'type' => $inactiveSubscriptions->getType(),
            'dateFin' => $inactiveSubscriptions->getDateFin()->format('Y-m-d H:i:s'),
            'code' => $inactiveSubscriptions->getModuleAbonnement()?->getCode(),
            'daysSinceExpiration' => (new \DateTime())->diff($inactiveSubscriptions->getDateFin())->days
        ];
        /*  }, $inactiveSubscriptions); */

        return $formattedSubscriptions;
    }

    public function getActiveSubscription(Entreprise $entreprise): ?Abonnement
    {
        //dd("entreprise", $entreprise);
        $activeSubscriptions = $this->abonnementRepository->findActiveForEntreprise($entreprise);
        return $activeSubscriptions;
    }

    public function checkFeatureAccess(Entreprise $entreprise): void
    {
        $abonnement = $this->getActiveSubscription($entreprise);

        if (!$abonnement) {
            throw new HttpException(403, 'Abonnement requis pour cette fonctionnalité');
        }

        // Vérification des modules/fonctionnalités spécifiques
        /*  $module = $abonnement->getModuleAbonnement();
        if (!$module || !$module->hasFeature($feature)) {
            throw new HttpException(403, sprintf(
                'Votre abonnement "%s" ne donne pas accès à cette fonctionnalité',
                $abonnement->getType()
            ));
        } */
    }
}

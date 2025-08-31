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

    public function getInactiveSubscriptions(Entreprise $entreprise): array
    {
        return $this->abonnementRepository->findBy([
            'entreprise' => $entreprise,
            'etat' => 'inactif'
        ], ['dateFin' => 'DESC']);
    }

    public function checkInactiveSubscription(Entreprise $entreprise): array
    {
        $inactiveSubscriptions = $this->getInactiveSubscriptions($entreprise);

        if (empty($inactiveSubscriptions)) {
            return [];
        }

        // Formater les données pour la réponse
        $formattedSubscriptions = array_map(function (Abonnement $abonnement) {
            return [
                'id' => $abonnement->getId(),
                'type' => $abonnement->getType(),
                'dateFin' => $abonnement->getDateFin()->format('Y-m-d H:i:s'),
                'code' => $abonnement->getModuleAbonnement()?->getCode(),
                'daysSinceExpiration' => (new \DateTime())->diff($abonnement->getDateFin())->days
            ];
        }, $inactiveSubscriptions);

        return $formattedSubscriptions;
    }

    public function getActiveSubscription(Entreprise $entreprise): ?Abonnement
    {
        return $this->abonnementRepository->findActiveForEntreprise($entreprise);
    }

    public function checkFeatureAccess(Entreprise $entreprise, string $feature): void
    {
        $abonnement = $this->getActiveSubscription($entreprise);

        if (!$abonnement || !$abonnement->getEtat()) {
            throw new HttpException(403, 'Abonnement requis pour cette fonctionnalité');
        }

        // Vérification des modules/fonctionnalités spécifiques
        $module = $abonnement->getModuleAbonnement();
        if (!$module || !$module->hasFeature($feature)) {
            throw new HttpException(403, sprintf(
                'Votre abonnement "%s" ne donne pas accès à cette fonctionnalité',
                $abonnement->getType()
            ));
        }
    }
}

<?php

namespace App\Service;

use App\Entity\PaiementAbonnement;
use App\Repository\AbonnementRepository;
use App\Repository\PaiementAbonnementRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SendMailService;

class RenouvellementService
{
    private PaiementAbonnementRepository $paiementAbonnementRepository;
    private AbonnementRepository $abonnementRepository;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SendMailService $sendMailService;

    public function __construct(
       
        PaiementAbonnementRepository $paiementAbonnementRepository,
        AbonnementRepository $abonnementRepository,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SendMailService $sendMailService,
    ) {

        $this->paiementAbonnementRepository = $paiementAbonnementRepository;
        $this->abonnementRepository = $abonnementRepository;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sendMailService = $sendMailService;
    }

    public function updateData(): string
    {
        $now = new \DateTime();
        $compteur = 0;

        $abonnementActifs = $this->abonnementRepository->findBy(['etat' => 'actif']);

        foreach ($abonnementActifs as $abonnement) {
            
            $dateFin = $abonnement->getDateFin();
            $diff = $dateFin->diff($now);

            if ($diff->y >= 1) {
                   
                $abonnement->setEtat('inactif');
                $this->entityManager->persist($abonnement);
              
                    $user_message = [
                        'message' => "Bonjour " . $abonnement->getEntreprise()->getEmail() . ", votre abonnement a expiré. Nous vons invitons au renouvellement.",
                    ];
                    $context = compact('user_message');

                    $this->sendMailService->send(
                        'depps@myonmci.ci',
                        $abonnement->getEntreprise()->getEmail(),
                        'Informations - Renouvellement Abonnement',
                        'renew_mail',
                        $context
                    );

                    $compteur++;
                }
        }

        // Persiste les modifications
        $this->entityManager->flush();

        return "$compteur professionnel(s) ont été mis à jour pour renouvellement.";
    }
}

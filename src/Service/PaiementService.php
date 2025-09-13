<?php

namespace App\Service;

use App\Entity\Abonnement;
use App\Entity\ModuleAbonnement;
use App\Entity\Paiement;
use App\Entity\PaiementAbonnement;
use App\Entity\User;
use App\Repository\AbonnementRepository;
use App\Repository\ModuleAbonnementRepository;
use App\Repository\PaiementAbonnementRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaiementService
{

    private string $apiKey;
    private string $merchantId;
    private string $paiementUrl;
    public function __construct(
        private ParameterBagInterface $params,
        private UrlGeneratorInterface $urlGenerator,
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em,
        private PaiementAbonnementRepository $paiementAbonnementRepository,
        private ModuleAbonnementRepository $moduleAbonnementRepository,
        private AbonnementRepository $abonnementRepository,
        private SendMailService $sendMailService,
        private AddCategorie $addCategorie,
        private PaysRepository $paysRepository
    ) {

        $this->apiKey = $params->get('API_KEY');
        $this->merchantId = $params->get('MERCHANT_ID');
        $this->paiementUrl = $params->get('PAIEMENT_URL');
    }

    public function generateReference(string $code): string
    {
        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Paiement::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        return ($code . date("y") . date("m") . date("d") . date("H") . date("i") . date("s") . str_pad($nb + 1, 3, '0', STR_PAD_LEFT));
    }

    public function traiterPaiement($data = [], User $user, ModuleAbonnement $moduleAbonnement): array
    {
        $paiement = new PaiementAbonnement();
        $paiement->setMontant($moduleAbonnement->getMontant());
        $paiement->setModuleAbonnement($moduleAbonnement);
        $paiement->setCreatedAtValue(new \DateTime());
        $reference = $this->generateReference('ABNT');
        $paiement->setReference($reference);
        $paiement->setPays('CI');
        $paiement->setChannel($data['operateur']);
        $paiement->setCreatedBy($user);
        $paiement->setUpdatedBy($user);
        $paiement->setType("Abonnement");
        $paiement->setDataBoutique($data['dataBoutique']);
        $paiement->setDataSuccursale($data['dataSuccursale']);
        $paiement->setDataUser($data['dataUser']);
        $paiement->setState(0);


        try {
            $client = new \SoapClient($this->paiementUrl . '?wsdl', [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => 1,
                'exceptions' => true
            ]);

            $requestData = [
                'merchantId'           => $this->merchantId,
                'referenceNumber'      => $reference,
                'amount'               => 100,
                'channel'              => $data['operateur'], // ex: CARD, MOBILE
                'countryCurrencyCode'  => '952',
                'currency'             => 'XOF',
                'customerId'           => (string) $user->getId(),
                'customerFirstName'    => $data['entrepriseDenomination'],
                'customerLastname'     => $data['entrepriseDenomination'],
                'customerEmail'        => $data['email'],
                'customerPhoneNumber'  => $data['numero'],
                'description'          => 'Abonnement ' . $moduleAbonnement->getCode(),
                'notificationURL'      => $this->urlGenerator->generate('webhook_paiement', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'returnURL'            => 'https://ton-site.com/paiement/retour',
                'returnContext'        => http_build_query([
                    'paiement_id' => $paiement->getId(),
                    'user_id'     => $user->getId()
                ]),
                /*  'api'                  => $this->apiKey,
                'service'              => 'PAIEMENT', */
            ];

            $response = $client->initTransact($requestData);


            if ($response->Code == 0) {
                $this->paiementAbonnementRepository->add($paiement, true);
            }

            $paiementProUrl = 'https://www.paiementpro.net/webservice/onlinepayment/processing_v2.php?sessionid=' . $response->Sessionid;
            $sessionId = $response->Sessionid;

            return [
                'code'        => 200,
                'reference'   => $reference,
                'transaction_id' => $sessionId,
                'redirectUrl' => $paiementProUrl,
                'sessionId'   => $sessionId
            ];
        } catch (\SoapFault $e) {
            return [
                'code' => 400,
                'error' => $e->getMessage(),
                'reference' => $reference
            ];
        }
    }



    public function methodeWebHook(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $paiement = $this->paiementAbonnementRepository->findOneBy(['reference' => $data['referenceNumber']]);

        if ($data['responsecode'] == 0) {
            $paiement->setState(1);

            $paiement->setChannel($data['channel']);
            //$transaction->setData(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->paiementAbonnementRepository->add($paiement, true);
            // JE dois mettre a jour l'abonnement
            $this->createAbonnement($data['referenceNumber']);
        } else {
            $response = [
                'message' => 'Echec',
                'code' => 400
            ];
        }


        return $response;
    }

    public function createAbonnement($ref)
    {

        // je dois desactiver les user ,boutique et succursale
        //je dois recuperer les parametres de l'abonnement
        //je dois renseigner setting 

        $paiement = $this->paiementAbonnementRepository->findOneBy(['reference' => $ref]);

        $abonnementActif = $this->abonnementRepository->findOneBy(['moduleAbonnement' => $paiement->getModuleAbonnement(), 'entreprise' => $paiement->getEntreprise(), 'etat' => 'actif']);

        $abonnement = new Abonnement();
        $abonnement->setModuleAbonnement($paiement->getModuleAbonnement());
        $abonnement->setEntreprise($paiement->getEntreprise());
        $abonnement->setType('payant');
        $abonnement->setEtat('actif');

        if ($abonnementActif == null) {
            $abonnement->setDateFin((new \DateTime())->modify('+' . $paiement->getModuleAbonnement()->getDuree() . ' month'));
        } else {
            $abonnement->setDateFin($abonnementActif->getDateFin()->modify('+' . $paiement->getModuleAbonnement()->getDuree() . ' month'));
            $abonnementActif->setEtat('inactif');
            $this->abonnementRepository->add($abonnementActif, true);
        }
        $this->abonnementRepository->add($abonnement, true);


        $nombreSms = 0;
        $nombreUser = 0;
        $nombresuccursale = 0;

        foreach ($paiement->getModuleAbonnement()->getLigneModules() as  $ligneModule) {
            $nombreSms = $ligneModule->getLibelle() == "SMS" ? $ligneModule->getQuantite() : 0;
            $nombreUser = $ligneModule->getLibelle() == "USER" ? $ligneModule->getQuantite() : 0;
            $nombresuccursale = $ligneModule->getLibelle() == "SUCCURSALE" ? $ligneModule->getQuantite() : 0;
            $nombreBoutique = $ligneModule->getLibelle() == "BOUTIQUE" ? $ligneModule->getQuantite() : 0;
        }


        $this->addCategorie->setting($paiement->getEntreprise(), [
            'succursale' => $nombresuccursale,
            'user' => $nombreUser,
            'sms' => $nombreSms,
            'boutique' => $nombreBoutique
        ]);

        $this->sendMailService->sendNotification([
            'libelle' => "Bienvenue dans notre application",
            'titre' => "Bienvenue",
            'entreprise' => $paiement->getEntreprise(),
            'user' => "",
            'userUpdate' => ""
        ]);
    }
}

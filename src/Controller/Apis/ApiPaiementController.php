<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\PaiementFactureDTO;
use App\Entity\Abonnement;
use App\Entity\Facture;
use App\Entity\ModuleAbonnement;
use App\Entity\PaiementAbonnement;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\PaiementFacture;
use App\Repository\AbonnementRepository;
use App\Repository\FactureRepository;
use App\Repository\PaiementFactureRepository;
use App\Repository\TypeUserRepository;
use App\Repository\UserRepository;
use App\Service\Utils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/paiement')]
class ApiPaiementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des paiements.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PaiementFacture::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiement')]
    // #[Security(name: 'Bearer')]
    public function index(PaiementFactureRepository $paiementRepository): Response
    {
        try {

            $paiements = $paiementRepository->findAll();

            $response =  $this->responseData($paiements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/entreprise', methods: ['GET'])]
    /**
     * Retourne la liste des typeMesures d'une entreprise.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PaiementFacture::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiement')]
    // #[Security(name: 'Bearer')]
    public function indexAll(PaiementFactureRepository $paiementRepository, TypeUserRepository $typeUserRepository): Response
    {
         $inactiveSubscriptions = $this->subscriptionChecker->checkInactiveSubscription($this->getUser()->getEntreprise());

        try {
            if ($this->getUser()->getType() == $typeUserRepository->findOneBy(['code' => 'ADM'])) {
                $paiements = $paiementRepository->findBy(
                    ['entreprise' => $this->getUser()->getEntreprise()],
                    ['id' => 'ASC']
                );
            } else {
                $paiements = $paiementRepository->findBy(
                    ['surccursale' => $this->getUser()->getSurccursale()],
                    ['id' => 'ASC']
                );
            }


            $response =  $this->responseDataWith_([
                'data' => $paiements,
                'inactiveSubscriptions' => $inactiveSubscriptions
            ], 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }




    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) paiement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) paiement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PaiementFacture::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'paiement')]
    //#[Security(name: 'Bearer')]
    public function getOne(?PaiementFacture $paiement)
    {
        try {
            if ($paiement) {
                $response =  $this->responseData($paiement, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($paiement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/facture/{id}',  methods: ['POST'])]
    /**
     * Permet de créer un(e) paiement.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "montant", type: "string"),
                    new OA\Property(property: "factureId", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiement')]
    public function create(Request $request, Utils $utils, Facture $facture, FactureRepository $factureRepository, PaiementFactureRepository $paiementRepository): Response
    {

        $inactiveSubscriptions = $this->subscriptionChecker->checkInactiveSubscription($this->getUser()->getEntreprise());
        $data = json_decode($request->getContent(), true);
        $paiement = new PaiementFacture();
        $paiement->setMontant($data['montant']);
        $paiement->setFacture($facture);
        $paiement->setReference($utils->generateReference('PMT'));
        $paiement->setCreatedBy($this->getUser());
        $paiement->setUpdatedBy($this->getUser());

        $facture->setResteArgent((int)$facture->getResteArgent() - (int)$data['montant']);


        $errorResponse = $this->errorResponse($paiement);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $paiementRepository->add($paiement, true);
            $factureRepository->add($facture, true);
        }

        return  $this->responseDataWith_([
                'data' => $facture,
                'inactiveSubscriptions' => $inactiveSubscriptions
            ], 'group1', ['Content-Type' => 'application/json']);;
    }

    #[Route('/abonnement/{id}',  methods: ['POST'])]
    /**
     * Permet de créer un(e) paiement.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "montant", type: "string"),
                    new OA\Property(property: "factureId", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiement')]
    public function createAbonnement(Request $request, AbonnementRepository $abonnementRepository, Utils $utils, ModuleAbonnement $moduleAbonnement, FactureRepository $factureRepository, PaiementFactureRepository $paiementRepository): Response
    {
        $inactiveSubscriptions = $this->subscriptionChecker->checkInactiveSubscription($this->getUser()->getEntreprise());
        $data = json_decode($request->getContent(), true);
        $paiement = new PaiementAbonnement();
        $paiement->setMontant($data['montant']);
        $paiement->setModuleAbonnement($moduleAbonnement);
        $paiement->setCreatedAtValue(new \DateTime());
        $paiement->setReference($utils->generateReference('PMT'));
        $paiement->setCreatedBy($this->getUser());
        $paiement->setUpdatedBy($this->getUser());

        $abonnement = $$abonnementRepository->findOneBy(['moduleAbonnement' => $moduleAbonnement, 'entreprise' => $this->getUser()->getEntreprise()]);

        if ($abonnement == null) {
            $abonnement = new Abonnement();
            $abonnement->setModuleAbonnement($moduleAbonnement);
            $abonnement->setEntreprise($this->getUser()->getEntreprise());
            $abonnement->setDateFin((new \DateTime())->modify('+' . $moduleAbonnement->getDuree() . ' month'));
            $abonnement->setType('payant');
            $abonnement->setEtat('actif');
        } else {
            $abonnement->getEtat() != "actif" ? $abonnement->setDateFin((new \DateTime())->modify('+' . $moduleAbonnement->getDuree() . ' month')) : $abonnement->setDateFin($abonnement->getDateFin()->modify('+' . $moduleAbonnement->getDuree() . ' month'));
            $abonnement->setType('payant');
        }

        $errorResponse = $this->errorResponse($paiement);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {
            $paiementRepository->add($paiement, true);
        }

        return    $this->responseDataWith_([
                'data' => $abonnement,
                'inactiveSubscriptions' => $inactiveSubscriptions
            ], 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) paiement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) paiement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PaiementFacture::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiement')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, PaiementFacture $paiement, PaiementFactureRepository $villeRepository): Response
    {
        try {

            if ($paiement != null) {

                $villeRepository->remove($paiement, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($paiement);
            } else {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs paiement.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PaiementFacture::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiement')]
    public function deleteAll(Request $request, PaiementFactureRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $paiement = $villeRepository->find($value['id']);

                if ($paiement != null) {
                    $villeRepository->remove($paiement);
                }
            }
            $this->setMessage("Operation effectuées avec success");
            $response = $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
}

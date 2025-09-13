<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Caisse;
use App\Entity\CaisseSuccursale;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Surccursale;
use App\Repository\CaisseSuccursaleRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\SurccursaleRepository;
use App\Repository\UserRepository;
use App\Service\Utils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model as Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\Attribute\Model as AttributeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/surccursale')]
class ApiSurccursaleController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des surccursales.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'surccursale')]
    // #[Security(name: 'Bearer')]
    public function index(SurccursaleRepository $surccursaleRepository): Response
    {
        try {

            $surccursales = $surccursaleRepository->findAll();



            $response =  $this->responseData($surccursales, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/active/entreprise', methods: ['GET'])]
    /**
     * Retourne la liste des surccursales actives d'une entreprise.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeMesure')]
    // #[Security(name: 'Bearer')]
    public function indexAllActive(SurccursaleRepository $surccursaleRepository): Response
    {
        try {

            $surccursales = $surccursaleRepository->findBy(
                ['entreprise' => $this->getUser()->getEntreprise(), 'active' => true],
                ['id' => 'ASC']
            );



            $response =  $this->responseData($surccursales, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/entreprise', methods: ['GET'])]
    /**
     * Retourne la liste des surccursales d'une entreprise.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeMesure')]
    // #[Security(name: 'Bearer')]
    public function indexAll(SurccursaleRepository $surccursaleRepository): Response
    {
        try {

            $surccursales = $surccursaleRepository->findBy(
                ['entreprise' => $this->getUser()->getEntreprise()],
                ['id' => 'ASC']
            );



            $response =  $this->responseData($surccursales, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }





    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) surccursale en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) surccursale en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'surccursale')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Surccursale $surccursale)
    {
        try {
            if ($surccursale) {
                $response = $this->response($surccursale);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($surccursale);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) surccursale.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "contact", type: "string"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'surccursale')]
    #[Security(name: 'Bearer')]
    public function create(Request $request,Utils $utils,CaisseSuccursaleRepository $caisseSuccursaleRepository, SurccursaleRepository $surccursaleRepository, EntrepriseRepository $entrepriseRepository): Response
    {
        if ($this->subscriptionChecker->getActiveSubscription($this->getUser()->getEntreprise()) == null) {
            return $this->errorResponseWithoutAbonnement('Abonnement requis pour cette fonctionnalité');
        }

        $this->allParametres('surccursale');

        $data = json_decode($request->getContent(), true);
        $surccursale = new Surccursale();
        $surccursale->setLibelle($data['libelle']);
        $surccursale->setContact($data['contact']);
        $surccursale->setEntreprise($this->getUser()->getEntreprise());
        $surccursale->setCreatedBy($this->getUser());
        $surccursale->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($surccursale);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {


            $surccursaleRepository->add($surccursale, true);

            $caisse = new CaisseSuccursale();
            $caisse->setMontant("0"); 
            $caisse->setSuccursale($surccursale);
            $caisse->setReference($utils->generateReference('CAIS'));
            $caisse->setType(Caisse::TYPE['succursale']);
            $caisse->setEntreprise($this->getUser()->getEntreprise());
            $caisse->setCreatedBy($this->getUser());
            $caisse->setUpdatedBy($this->getUser());

            $caisseSuccursaleRepository->add($caisse, true);
      
        }

        return $this->responseData($surccursale, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de surccursale",
        description: "Permet de créer un surccursale.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "contact", type: "string"),


                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'surccursale')]
    #[Security(name: 'Bearer')]
    public function update(Request $request, Surccursale $surccursale, SurccursaleRepository $surccursaleRepository, EntrepriseRepository $entrepriseRepository): Response
    {
        if ($this->subscriptionChecker->getActiveSubscription($this->getUser()->getEntreprise()) == null) {
            return $this->errorResponseWithoutAbonnement('Abonnement requis pour cette fonctionnalité');
        }

        try {
            $data = json_decode($request->getContent());
            if ($surccursale != null) {

                $surccursale->setLibelle($data->libelle);
                $surccursale->setContact($data->contact);
                $surccursale->setEntreprise($this->getUser()->getEntreprise());
                $surccursale->setUpdatedBy($this->getUser());
                $surccursale->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($surccursale);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $surccursaleRepository->add($surccursale, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($surccursale, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage("Cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) surccursale.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) surccursale',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'surccursale')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Surccursale $surccursale, SurccursaleRepository $villeRepository): Response
    {
        if ($this->subscriptionChecker->getActiveSubscription($this->getUser()->getEntreprise()) == null) {
            return $this->errorResponseWithoutAbonnement('Abonnement requis pour cette fonctionnalité');
        }

        try {

            if ($surccursale != null) {

                $villeRepository->remove($surccursale, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($surccursale);
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
     * Permet de supprimer plusieurs surccursale.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Surccursale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'surccursale')]
    #[Security(name: 'Bearer')]
    public function deleteAll(Request $request, SurccursaleRepository $villeRepository): Response
    {

        if ($this->subscriptionChecker->getActiveSubscription($this->getUser()->getEntreprise()) == null) {
            return $this->errorResponseWithoutAbonnement('Abonnement requis pour cette fonctionnalité');
        }

        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $surccursale = $villeRepository->find($value['id']);

                if ($surccursale != null) {
                    $villeRepository->remove($surccursale);
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

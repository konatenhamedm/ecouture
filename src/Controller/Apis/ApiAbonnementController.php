<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Abonnement;
use App\Repository\AbonnementRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model as Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\Attribute\Model as AttributeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/abonnement')]
class ApiAbonnementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des modules.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Abonnement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'abonnement')]
    // #[Security(name: 'Bearer')]
    public function index(AbonnementRepository $moduleRepository): Response
    {
        try {

            $categories = $moduleRepository->findAll();

          

            $response =  $this->responseData($categories, 'group1', ['Content-Type' => 'application/json']);
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
            items: new OA\Items(ref: new AttributeModel(type: Abonnement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'abonnement')]
    // #[Security(name: 'Bearer')]
    public function indexAll(AbonnementRepository $moduleRepository): Response
    {
        try {

            $typeMesures = $moduleRepository->findBy(
                ['entreprise' => $this->getUser()->getEntreprise()],
                ['id' => 'ASC']
            );

          

            $response =  $this->responseData($typeMesures, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

     #[Route('/entreprise/actif', methods: ['GET'])]
    /**
     * Retourne la liste des typeMesures d'une entreprise.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Abonnement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'abonnement')]
    // #[Security(name: 'Bearer')]
    public function indexActif(AbonnementRepository $moduleRepository): Response
    {
        try {

            $typeMesures = $moduleRepository->findBy(
                ['entreprise' => $this->getUser()->getEntreprise(),'etat' => 'actif'],
                ['id' => 'ASC']
            );

          

            $response =  $this->responseData($typeMesures, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
   



}

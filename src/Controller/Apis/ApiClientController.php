<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ClientDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\TypeUserRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/client')]
class ApiClientController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des clients.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'client')]
    // #[Security(name: 'Bearer')]
    public function index(ClientRepository $clientRepository): Response
    {
        try {

            $clients = $clientRepository->findAll();



            $response =  $this->responseData($clients, 'group1', ['Content-Type' => 'application/json']);
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
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'client')]
    // #[Security(name: 'Bearer')]
    public function indexAll(ClientRepository $clientRepository, TypeUserRepository $typeUserRepository): Response
    {
        try {
            if ($this->getUser()->getType() == $typeUserRepository->findOneBy(['code' => 'ADM'])) {


                $clients = $clientRepository->findBy(
                    ['entreprise' => $this->getUser()->getEntreprise()],
                    ['id' => 'ASC']
                );
            } else {
                $clients = $clientRepository->findBy(
                    ['surccursale' => $this->getUser()->getSurccursale()],
                    ['id' => 'ASC']
                );
            }


            $response =  $this->responseData($clients, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) client en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) client en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'client')]
    //#[Security(name: 'Bearer')]
    public function getOne(?Client $client)
    {
        try {
            if ($client) {
                $response = $this->response($client);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($client);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) client.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "numero", type: "string"),
                    new OA\Property(property: "surccursale", type: "string")

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'client')]
    public function create(Request $request, ClientRepository $clientRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $client = new Client();
        $client->setNom($data['nom']);
        $client->setNumero($data['numero']);
        $client->setSurccursale($data['surccursale']);
        $client->setCreatedBy($this->getUser());
        $client->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($client);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $clientRepository->add($client, true);
        }

        return $this->responseData($client, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de client",
        description: "Permet de créer un client.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "numero", type: "string"),
                    new OA\Property(property: "surccursale", type: "string")

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'client')]
    public function update(Request $request, Client $client, ClientRepository $clientRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($client != null) {

                $client->setNom($data->nom);
                $client->setNumero($data->numero);
                $client->setSurccursale($data->surccursale);
                $client->setUpdatedBy($this->getUser());
                $client->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($client);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $clientRepository->add($client, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($client, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) client.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'client')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Client $client, ClientRepository $villeRepository): Response
    {
        try {

            if ($client != null) {

                $villeRepository->remove($client, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($client);
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
     * Permet de supprimer plusieurs client.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'client')]
    public function deleteAll(Request $request, ClientRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $client = $villeRepository->find($value['id']);

                if ($client != null) {
                    $villeRepository->remove($client);
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

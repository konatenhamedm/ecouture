<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model as Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\Attribute\Model as AttributeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/notification')]
class ApiNotificationController extends ApiInterface
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
            items: new OA\Items(ref: new AttributeModel(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    // #[Security(name: 'Bearer')]
    public function index(NotificationRepository $moduleRepository): Response
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


     #[Route('/user', methods: ['GET'])]
    /**
     * Retourne la liste des typeMesures d'une entreprise.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    // #[Security(name: 'Bearer')]
    public function indexAll(NotificationRepository $moduleRepository): Response
    {
        try {

            $typeMesures = $moduleRepository->findBy(
                ['user' => $this->getUser()],
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
   


    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) notification.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) notification',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    //#[Security(name: 'Bearer')]
    public function delete(Request $request, Notification $notification, NotificationRepository $villeRepository): Response
    {
        try {

            if ($notification != null) {

                $villeRepository->remove($notification, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($notification);
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
     * Permet de supprimer plusieurs notification.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    #[Security(name: 'Bearer')]
    public function deleteAll(Request $request, NotificationRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $notification = $villeRepository->find($value['id']);

                if ($notification != null) {
                    $villeRepository->remove($notification);
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

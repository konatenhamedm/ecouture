<?php

namespace App\Controller;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Setting;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\SubscriptionChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;


class AuthController extends ApiInterface
{


    #[Route('/api/login', methods: ['POST'])]
    #[OA\Post(
        summary: "Permet d'authentifier un utilisateur",
        description: "Permet d'authentifier un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "login",
                        type: "string",
                        default: "0101564767" 
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        default: "admin93K" 
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    #[OA\Tag(name: 'auth')]
    public function login(
        Request $request,
        JwtService $jwtService,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo,
        SubscriptionChecker $subscriptionChecker,
        SettingRepository $settingRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepo->findOneBy(['login' => $data['login']]);

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        } elseif (!$user->isActive()) {

            return $this->errorResponse($user, 'User is not active');
        }

        $token = $jwtService->generateToken([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles()
        ]);

        $inactiveSubscriptions = $subscriptionChecker->checkInactiveSubscription($user->getEntreprise());
        $activeSubscriptions = $subscriptionChecker->getActiveSubscription($user->getEntreprise());
        /* dd($this->json([$activeSubscriptions])); */

        return $this->responseData([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'roles' => $user->getRoles(),
                'is_active' => $user->isActive(),
                /* 'inactiveSubscriptions' => $inactiveSubscriptions, */
                'pays' => $user->getEntreprise()->getPays()->getId(),
                'boutique' => $user->getBoutique() ? $user->getBoutique()->getId() : null,
                'succursale' => $user->getSurccursale() ? $user->getSurccursale()->getId() : null,
                'settings' =>  $settingRepository->findOneBy(['entreprise' => $user->getEntreprise()]),
                'activeSubscriptions' => $activeSubscriptions
            ],
            'token_expires_in' => $jwtService->getTtl()
        ], 'group1', ['Content-Type' => 'application/json']);

        /*    return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'roles' => $user->getRoles(),
                'is_active' => $user->isActive(),
                'inactiveSubscriptions' => $inactiveSubscriptions,
                'settings' =>  $settingRepository->findOneBy(['entreprise' => $user->getEntreprise()])
                //'activeSubscriptions' => $activeSubscriptions
            ],
            'token_expires_in' => $jwtService->getTtl()
        ]); */
    }
}

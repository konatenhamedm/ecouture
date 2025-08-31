<?php

namespace App\Controller;

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


class AuthController extends AbstractController
{
  

    #[Route('/api/login', methods: ['POST'])]
      #[OA\Post(
        summary: "Login for a user",
        description: "login for a user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [

                    new OA\Property(property: "login", type: "string"),
                    new OA\Property(property: "password", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'auth')]
    public function login(
        Request $request,
        JwtService $jwtService,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo,SubscriptionChecker $subscriptionChecker
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepo->findOneBy(['login' => $data['login']]);

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtService->generateToken([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles()
        ]);

         $inactiveSubscriptions = $subscriptionChecker->checkInactiveSubscription($user->getEntreprise());
        

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'roles' => $user->getRoles(),
                'is_active' => $user->isActive(),
                'inactiveSubscriptions' => $inactiveSubscriptions
            ],
            'token_expires_in' => $jwtService->getTtl()
        ]);
    }
}

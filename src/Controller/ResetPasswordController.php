<?php

// src/Controller/Api/ResetPasswordController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use OpenApi\Attributes as OA;


#[Route('/api/reset-password')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer
    ) {
    }

   /*  #[Route('/requests', name: 'api_forgot_password_request', methods: ['POST'])]  */
     #[OA\Post(
        summary: "Request password reset",
        description: "Request password reset for a user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [

                    new OA\Property(property: "email", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'auth')]
    public function request(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (empty($email)) {
            return $this->json(
                ['message' => 'Email is required'], 
                Response::HTTP_BAD_REQUEST
            );
        }
        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $email]);
     

        // Ne pas révéler si l'utilisateur existe ou non
        if (!$user) {
            return $this->json(
                ['message' => 'Vous avez demandé un réinitialisation de mot de passe. Si l\'email existe, un lien de réinitialisation a été envoyé.'], 
                Response::HTTP_OK
            );
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
               //dd($resetToken);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(
                ['message' => 'Une erreur est survenue lors de la génération du token de réinitialisation.'], 
                Response::HTTP_BAD_REQUEST
            );
        }

      /*   $this->sendResetEmail($user, $resetToken); */

        return $this->json([
            'message' => 'Vous avez demandé un réinitialisation de mot de passe. Si l\'email existe, un lien de réinitialisation a été envoyé.',
            'token' => $resetToken->getToken() // À retirer en production
        ]);
    }

/*     #[Route('/resets', name: 'api_reset_password', methods: ['POST'])] */
     #[OA\Post(
        summary: "Reset user password",
        description: "Reset user password",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [

                    new OA\Property(property: "token", type: "string"),
                    new OA\Property(property: "password", type: "string")

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'auth')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (empty($token)) {
            return $this->json(
                ['message' => 'Token is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (empty($newPassword)) {
            return $this->json(
                ['message' => 'New password is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(
                ['message' => 'Invalid or expired token'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Encode et sauvegarde le nouveau mot de passe
        $user->setPassword(
            $passwordHasher->hashPassword($user, $newPassword)
        );
        $this->entityManager->flush();

        // Supprime le token après utilisation
        $this->resetPasswordHelper->removeResetRequest($token);

        return $this->json(
            ['message' => 'Password reset successfully']
        );
    }

    private function sendResetEmail(User $user, $resetToken): void
    {
        $email = (new Email())
            ->from(new Address('no-reply@yourdomain.com', 'Password Reset'))
            ->to($user->getLogin())
            ->subject('Your password reset request')
            ->text(sprintf(
                'To reset your password, use this token: %s',
                $resetToken->getToken()
            ))
            ->html(sprintf(
                '<p>To reset your password, use this token: <strong>%s</strong></p>',
                $resetToken->getToken()
            ));

        $this->mailer->send($email);
    }
}
<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\PasswordReset;
use App\Repository\AdminRepository;
use App\Repository\PasswordResetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/password-reset', name: 'app_password_reset_request', methods: ['GET', 'POST'])]
    public function request(
        Request $request,
        AdminRepository $adminRepository,
        PasswordResetRepository $resetRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $admin = $adminRepository->findOneBy(['email' => $email]);

            // Check if email belongs to an admin
            if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
                // Always show success message even if email not found (security)
                $this->addFlash('success', 'Eine E-Mail mit weiteren Anweisungen wurde an Ihre E-Mail-Adresse gesendet.');
                return $this->redirectToRoute('app_login');
            }

            // Check IP limit
            $ipAddress = $request->getClientIp();
            $activeRequests = $resetRepository->countActiveRequestsByIp($ipAddress);

            if ($activeRequests >= 2) {
                $this->addFlash('error', 'Zu viele Anfragen. Bitte warten Sie, bis die vorherigen Anfragen abgelaufen sind.');
                return $this->redirectToRoute('app_password_reset_request');
            }

            // Create password reset request
            $passwordReset = new PasswordReset();
            $passwordReset->setEmail($email);
            $passwordReset->setToken($tokenGenerator->generateToken());
            $passwordReset->setIpAddress($ipAddress);

            $entityManager->persist($passwordReset);
            $entityManager->flush();

            // Send email
            $email = (new TemplatedEmail())
                ->to($admin->getEmail())
                ->subject('Passwort zurücksetzen')
                ->htmlTemplate('emails/password_reset.html.twig')
                ->context([
                    'title' => 'Passwort zurücksetzen',
                    'resetUrl' => $this->generateUrl('app_password_reset_reset', [
                        'token' => $passwordReset->getToken()
                    ], 0),
                    'expiresAt' => $passwordReset->getExpiresAt()
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Eine E-Mail mit weiteren Anweisungen wurde an Ihre E-Mail-Adresse gesendet.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password_reset_request.html.twig');
    }

    #[Route('/password-reset/{token}', name: 'app_password_reset_reset', methods: ['GET', 'POST'])]
    public function reset(
        string $token,
        Request $request,
        PasswordResetRepository $resetRepository,
        AdminRepository $adminRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $passwordReset = $resetRepository->findValidByToken($token);

        if (!$passwordReset) {
            throw new AccessDeniedException('Ungültiger oder abgelaufener Token.');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $admin = $adminRepository->findOneBy(['email' => $passwordReset->getEmail()]);

            if (!$admin) {
                throw new AccessDeniedException('Admin nicht gefunden.');
            }

            // Update password
            $hashedPassword = $passwordHasher->hashPassword($admin, $password);
            $admin->setPassword($hashedPassword);

            // Mark reset request as used
            $passwordReset->setUsed(true);

            $entityManager->flush();

            $this->addFlash('success', 'Ihr Passwort wurde erfolgreich zurückgesetzt.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password_reset.html.twig', [
            'token' => $token
        ]);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Ruestzeit;
use App\Entity\RuestzeitShareInvitation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RuestzeitShareController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator,
        private MailerInterface $mailer
    ) {
    }

    #[Route('/admin/ruestzeit/{id}/share', name: 'admin_ruestzeit_share')]
        public function share(Request $request, Ruestzeit $ruestzeit): Response
    {
        // Check if the current user is the owner
        if ($ruestzeit->getAdmin() !== $this->getUser()) {
            return $this->render('admin/share_invitation_error.html.twig', [
                'message' => 'Sie können nur Ihre eigenen Rüstzeiten freigeben.'
            ]);
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            
            // Check if there's already a pending invitation
            $existingInvitation = $this->entityManager->getRepository(RuestzeitShareInvitation::class)
                ->findPendingByEmailAndRuestzeit($email, $ruestzeit->getId());
            
            if (!$existingInvitation) {
                $invitation = new RuestzeitShareInvitation();
                $invitation->setRuestzeit($ruestzeit);
                $invitation->setEmail($email);
                
                $this->entityManager->persist($invitation);
                $this->entityManager->flush();

                // Send invitation email
                $email = (new TemplatedEmail())
                    ->to($invitation->getEmail())
                    ->subject('Rüstzeit Freigabe Einladung')
                    ->htmlTemplate('emails/ruestzeit_share_invitation.html.twig')
                    ->context([
                        'title' => "Freigabe einer Rüstzeit für Zugriff",
                        'invitation' => $invitation,
                        'ruestzeit' => $ruestzeit,
                    ]);

                $this->mailer->send($email);

                $this->addFlash('success', 'Einladung wurde versendet.');
            } else {
                $this->addFlash('warning', 'Es existiert bereits eine ausstehende Einladung für diese E-Mail-Adresse.');
            }

            return $this->redirect($this->adminUrlGenerator
                ->setController(RuestzeitCrudController::class)
                ->setAction('index')
                ->generateUrl());
        }

        return $this->render('admin/ruestzeit_share.html.twig', [
            'ruestzeit' => $ruestzeit
        ]);
    }

    #[Route('/admin/share-invitation/{token}/revoke', name: 'admin_ruestzeit_revoke_invitation', methods: ['POST'])]
    public function revokeInvitation(string $token): Response
    {
        $invitation = $this->entityManager->getRepository(RuestzeitShareInvitation::class)
            ->findByToken($token);

        if (!$invitation) {
            $this->addFlash('error', 'Einladung nicht gefunden.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(RuestzeitCrudController::class)
                ->setAction('index')
                ->generateUrl());
        }

        // Check if the current user is the owner of the Ruestzeit
        if ($invitation->getRuestzeit()->getAdmin() !== $this->getUser()) {
            return $this->render('admin/share_invitation_error.html.twig', [
                'message' => 'Sie können nur Einladungen für Ihre eigenen Rüstzeiten zurückziehen.'
            ]);
        }

        $this->entityManager->remove($invitation);
        $this->entityManager->flush();

        $this->addFlash('success', 'Die Einladung wurde zurückgezogen.');

        return $this->redirect($this->adminUrlGenerator
            ->setRoute('admin_ruestzeit_share', ['id' => $invitation->getRuestzeit()->getId()])
            ->generateUrl());
    }

    #[Route('/admin/share-invitation/{token}', name: 'admin_share_invitation_accept')]
    public function acceptInvitation(string $token): Response
    {
        $invitation = $this->entityManager->getRepository(RuestzeitShareInvitation::class)
            ->findByToken($token);

        if (!$invitation || $invitation->isAccepted()) {
            return $this->render('admin/share_invitation_error.html.twig', [
                'message' => 'Die Einladung ist ungültig oder wurde bereits akzeptiert.'
            ]);
        }

        /** @var Admin $currentUser */
        $currentUser = $this->getUser();
        
        // Check if the invitation email matches the current user's email
        if ($invitation->getEmail() !== $currentUser->getEmail()) {
            return $this->render('admin/share_invitation_error.html.twig', [
                'message' => 'Diese Einladung wurde für eine andere E-Mail-Adresse erstellt. Bitte loggen Sie sich mit dem richtigen Account ein.'
            ]);
        }

        $ruestzeit = $invitation->getRuestzeit();
        
        // Add the current user to shared admins
        $ruestzeit->addSharedAdmin($currentUser);
        
        // Mark invitation as accepted
        $invitation->setAcceptedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();

        $this->addFlash('success', 'Sie haben nun Zugriff auf die Rüstzeit.');

        return $this->redirect($this->adminUrlGenerator
            ->setController(RuestzeitCrudController::class)
            ->setAction('index')
            ->generateUrl());
    }
}

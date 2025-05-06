<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Enum\PersonenTyp;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\AnmeldungRepository;
use App\Repository\RuestzeitRepository;
use App\Service\S3FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class MailingController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        protected AnmeldungRepository $anmeldungRepository,
        protected RuestzeitRepository $ruestzeitRepository,
        protected EntityManagerInterface $entityManager,
        protected S3FileUploader $s3FileUploader
    ) {
    }
    
    #[Route('/admin/mailing', name: 'admin_mailing')]
    public function index(): Response
    {
        $ruestzeit = $this->currentRuestzeitGenerator->get();
        
        // Get all anmeldungen for the current ruestzeit
        $anmeldungen = [];
        if ($ruestzeit) {
            $anmeldungen = $this->anmeldungRepository->findBy(['ruestzeit' => $ruestzeit]);
        }
        
        return $this->render('admin/mailing/index.html.twig', [
            'ruestzeit' => $ruestzeit,
            'anmeldungen' => $anmeldungen,
        ]);
    }
    
    #[Route('/admin/mailing/send', name: 'admin_mailing_send', methods: ['POST'])]
    public function send(Request $request): Response
    {
        $subject = $request->request->get('subject');
        $content = $request->request->get('content');
        $recipientType = $request->request->get('recipient_type');
        $individualRecipient = $request->request->get('individual_recipient');
        $customEmail = $request->request->get('custom_email');

        if (empty($subject) || empty($content)) {
            $this->addFlash('error', 'Betreff und Inhalt dürfen nicht leer sein.');
            return $this->redirect('/admin?routeName=admin_mailing');
        }
                
        if (empty($recipientType)) {
            $this->addFlash('error', 'Bitte wählen Sie einen Empfängertyp aus.');
            return $this->redirect('/admin?routeName=admin_mailing');
        }
        
        // Get recipients based on the selected type
        $recipients = $this->getRecipients($recipientType, $individualRecipient, $customEmail);
        
        if (empty($recipients)) {
            $this->addFlash('error', 'Keine Empfänger gefunden.');
            return $this->redirect('/admin?routeName=admin_mailing');
        }
        
        // Here would be the actual email sending logic
        // For now, we just display a success message with recipient count
        
        $this->addFlash('success', 'Versand wird durchgeführt an ' . count($recipients) . ' Empfänger');
        
        return $this->redirect('/admin?routeName=admin_mailing');
    }
    
    /**
     * Get recipients based on the selected type
     *
     * @param string $recipientType The type of recipients (individual, custom_email, mitarbeiter, active)
     * @param string|null $individualRecipientId The ID of the individual recipient if recipientType is 'individual'
     * @param string|null $customEmail The custom email address if recipientType is 'custom_email'
     * @return array Array of objects representing the recipients
     */
    private function getRecipients(string $recipientType, ?string $individualRecipientId, ?string $customEmail): array
    {
        $ruestzeit = $this->currentRuestzeitGenerator->get();
        
        if (!$ruestzeit) {
            return [];
        }
        
        switch ($recipientType) {
            case 'individual':
                if (empty($individualRecipientId)) {
                    return [];
                }
                $anmeldung = $this->anmeldungRepository->find($individualRecipientId);
                return $anmeldung ? [$anmeldung] : [];
                
            case 'custom_email':
                if (empty($customEmail) || !filter_var($customEmail, FILTER_VALIDATE_EMAIL)) {
                    return [];
                }
                // Create a simple object with email property to represent the custom email recipient
                $customRecipient = new \stdClass();
                $customRecipient->email = $customEmail;
                return [$customRecipient];
                
            case 'mitarbeiter':
                return $this->anmeldungRepository->findBy([
                    'ruestzeit' => $ruestzeit,
                    'personenTyp' => PersonenTyp::MITARBEITER,
                ]);
                
            case 'active':
                return $this->anmeldungRepository->findBy([
                    'ruestzeit' => $ruestzeit,
                    'status' => AnmeldungStatus::ACTIVE,
                ]);
                
            default:
                return [];
        }
    }
    
    #[Route('/admin/mailing/upload', name: 'admin_mailing_upload', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }
        
        try {
            $ruestzeit = $this->currentRuestzeitGenerator->get();
            
            if (!$ruestzeit) {
                return new JsonResponse(['error' => 'No active Ruestzeit found'], 400);
            }
            
            // Upload file to S3 in a mailing directory
            $key = $this->s3FileUploader->upload($file, 'mailing');
            
            // Create a new MailAttachment entity
            $mailAttachment = new \App\Entity\MailAttachment();
            $mailAttachment->setFilename($file->getClientOriginalName());
            $mailAttachment->setS3Key($key);
            $mailAttachment->setMimeType($file->getMimeType());
            $mailAttachment->setRuestzeit($ruestzeit);
            
            // Save to database
            $this->entityManager->persist($mailAttachment);
            $this->entityManager->flush();
            
            // Get the public URL of the uploaded file
            $url = $this->s3FileUploader->getPublicUrl($key);
            
            return new JsonResponse([
                'success' => true,
                'url' => $url,
                'filename' => $file->getClientOriginalName(),
                'uuid' => $mailAttachment->getUuid()->toRfc4122()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    #[Route('/admin/mailing/attachments', name: 'admin_mailing_attachments', methods: ['GET'])]
    public function listAttachments(): JsonResponse
    {
        try {
            $ruestzeit = $this->currentRuestzeitGenerator->get();
            
            if (!$ruestzeit) {
                return new JsonResponse(['error' => 'No active Ruestzeit found'], 400);
            }
            
            $attachments = [];
            
            foreach ($ruestzeit->getMailAttachments() as $attachment) {
                $attachments[] = [
                    'uuid' => $attachment->getUuid()->toRfc4122(),
                    'filename' => $attachment->getFilename(),
                    'url' => $this->s3FileUploader->getPublicUrl($attachment->getS3Key()),
                    'mimeType' => $attachment->getMimeType(),
                    'createdAt' => $attachment->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }
            
            return new JsonResponse([
                'success' => true,
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
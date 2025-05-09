<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Anmeldung;
use App\Entity\Mail;
use App\Entity\MailAttachment;
use App\Enum\AnmeldungStatus;
use App\Enum\PersonenTyp;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\AnmeldungRepository;
use App\Repository\MailAttachmentRepository;
use App\Repository\RuestzeitRepository;
use App\Service\S3FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

class MailingController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        protected AnmeldungRepository $anmeldungRepository,
        protected RuestzeitRepository $ruestzeitRepository,
        protected EntityManagerInterface $entityManager,
        protected S3FileUploader $s3FileUploader,
        protected MailerInterface $mailer,
        protected MailAttachmentRepository $mailAttachmentRepository,
        protected UrlGeneratorInterface $urlGenerator,
        protected \App\Service\MailProcessingService $mailProcessingService
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
            'subject' => "Information zu: " . $ruestzeit->getTitle(),
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
        $attachmentIds = $request->request->all('attachments') ?? [];

        //$requestContext = new RequestContext("/", "GET", $this->currentRuestzeitGenerator->get()->getDomain(), "https", 80, 443);
        //$this->urlGenerator->setContext($requestContext);

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

        // Get the current ruestzeit
        $ruestzeit = $this->currentRuestzeitGenerator->get();
        if (!$ruestzeit) {
            $this->addFlash('error', 'Keine aktive Rüstzeit gefunden.');
            return $this->redirect('/admin?routeName=admin_mailing');
        }

        // Get the current admin user
        /** @var Admin $admin */
        $admin = $this->getUser();
        
        // Get attachments if any
        $attachments = [];
        if (!empty($attachmentIds)) {
            foreach($attachmentIds as $attachmentId) {
                $attachments[] = $this->mailAttachmentRepository->findOneBy(['uuid' => $attachmentId]);
            }
        }
        
        // Process each recipient
        $sentCount = 0;
        foreach ($recipients as $recipient) {
            try {
                // Create a Mail entity for tracking
                $mail = new Mail();
                $mail->setSubject($subject);
                $mail->setContent($content);
                $mail->setRuestzeit($ruestzeit);
                $mail->setSender($admin);
                
                // Set recipient information
                if ($recipient instanceof Anmeldung) {
                    $mail->setRecipient($recipient);
                    $mail->setRecipientEmail($recipient->getEmail());
                    $mail->setRecipientName($recipient->getFirstname() . ' ' . $recipient->getLastname());
                } else {
                    // Custom email recipient
                    $mail->setRecipientEmail($recipient->email);
                }
                
                // Set sender information
                $mail->setSenderEmail($admin->getEmail());
                $mail->setSenderName($admin->getUsername()); // Use username as name since Admin doesn't have firstname/lastname
                
                // Add attachments
                foreach ($attachments as $attachment) {
                    // only add attachments, which are added in content
                    if(strpos($content, $attachment->getS3Key()) !== false) {
                    
                        $mail->addAttachment($attachment);

                    }
                }
                
                // Save the mail entity to get an ID
                $this->entityManager->persist($mail);
                $this->entityManager->flush();
                
                // Send the email with tracking
                $this->mailProcessingService->createAndSendEmail(
                    $mail
                );
                
                $sentCount++;
            } catch (\Exception $e) {
                dump($e);exit();
                // Log the error but continue with other recipients
                // In a production environment, you might want to log this to a file or monitoring service
                error_log('Error sending email: ' . $e->getMessage());
            }
        }
        
        if ($sentCount > 0) {
            $this->addFlash('success', 'E-Mail wurde an ' . $sentCount . ' Empfänger versendet.');
        } else {
            $this->addFlash('error', 'Es konnten keine E-Mails versendet werden.');
        }
        
        return $this->redirect('/admin?routeName=admin_mailing');
    }
    
    // Methods removed and moved to MailProcessingService
    
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
    
    #[Route('/admin/mailing/test', name: 'admin_mailing_test', methods: ['POST'])]
    public function sendTestEmail(Request $request): JsonResponse
    {
        try {
            $subject = $request->request->get('subject');
            $content = $request->request->get('content');
            $testEmail = $request->request->get('test_email');
            $attachmentIds = $request->request->all('attachments') ?? [];
            
            if (empty($subject) || empty($content) || empty($testEmail)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Betreff, Inhalt und Test-E-Mail-Adresse dürfen nicht leer sein.'
                ], 400);
            }
            
            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Ungültige E-Mail-Adresse.'
                ], 400);
            }
            
            // Get the current ruestzeit
            $ruestzeit = $this->currentRuestzeitGenerator->get();
            if (!$ruestzeit) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Keine aktive Rüstzeit gefunden.'
                ], 400);
            }
            
            // Get the current admin user
            /** @var Admin $admin */
            $admin = $this->getUser();
            
            // Create a temporary Mail entity for the test (will not be persisted)
            $mail = new Mail();
            $mail->setSubject($subject);
            $mail->setContent($content);
            $mail->setRuestzeit($ruestzeit);
            $mail->setSender($admin);
            $mail->setSenderEmail($admin->getEmail());
            $mail->setSenderName($admin->getUsername());
            $mail->setRecipientEmail($testEmail);
            
            // Set a special UUID for test emails that will be ignored in tracking
            $mail->setUuid(Uuid::fromString('00000000-0000-0000-0000-000000000000'));
            
            // Get attachments if any
            $attachments = [];
            if (!empty($attachmentIds)) {
                foreach($attachmentIds as $attachmentId) {
                    $attachment = $this->mailAttachmentRepository->findOneBy(['uuid' => $attachmentId]);

                    if ($attachment && strpos($content, $attachment->getS3Key()) !== false) {
                        $attachments[] = $attachment;
                        $mail->addAttachment($attachment);
                    }
                }
            }
            
            // Send the test email using the service
            $this->mailProcessingService->createAndSendEmail(
                $mail,
                true // Mark as test email
            );
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Test-E-Mail wurde erfolgreich gesendet.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Method removed and moved to MailProcessingService
}
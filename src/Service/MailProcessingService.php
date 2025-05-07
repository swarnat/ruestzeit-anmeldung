<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\MailAttachment;
use App\Repository\ConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailProcessingService
{
    public function __construct(
        private S3FileUploader $s3FileUploader,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private ConfigRepository $configRepository
    ) {
    }

    /**
     * Process email content to replace placeholders and add tracking
     */
    public function processContent(string $content, Mail $mail, string $trackingUrl): string
    {
        // Add the tracking image at the end of the content
        $trackingImage = '<img src="' . $trackingUrl . '" alt="" width="1" height="1" style="display:none;" />';
        
        // Process placeholders if a recipient is set
        $anmeldung = $mail->getRecipient();
        if ($anmeldung) {
            $content = str_ireplace("(vorname)", $anmeldung->getFirstname(), $content);
            $content = str_ireplace("(nachname)", $anmeldung->getLastname(), $content);
        } else {
            // For emails without a specific recipient, use placeholder values
            $content = str_ireplace("(vorname)", "[Vorname]", $content);
            $content = str_ireplace("(nachname)", "[Nachname]", $content);
        }
        
        return $content . $trackingImage;
    }

    /**
     * Process attachments and update content with tracking URLs
     */
    public function processAttachments(string $content, Mail $mail, string $trackingId): string
    {
        $attachments = $mail->getAttachments();

        foreach ($attachments as $attachment) {
            $originalUrl = $this->s3FileUploader->getPublicUrl($attachment->getS3Key());
            $attachmentUrl = $this->urlGenerator->generate(
                'mail_tracking_attachment',
                [
                    'attachmentId' => $attachment->getUuid()->toRfc4122(),
                    'trackingId' => $trackingId,
                    'filename' => $attachment->getFilename()
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            
            $content = str_replace($originalUrl, $attachmentUrl, $content);
        }
        
        return $content;
    }

    /**
     * Create and send an email
     */
    public function createAndSendEmail(
        Mail $mail, 
        bool $isTest = false
    ): void {
        $trackingId = $mail->getUuid()->toRfc4122();
        
        # When tracking ID is filled with zero, then use "draft" as tracking ID
        if($trackingId == "00000000-0000-0000-0000-000000000000") {
            $trackingId = "draft";
        }

        $ruestzeit = $mail->getRuestzeit();

        // Generate tracking URL
        $trackingUrl = $this->urlGenerator->generate(
            'mail_tracking_open',
            ['trackingId' => $trackingId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $attachments = $mail->getAttachments();
        
        // Process content with tracking
        $processedContent = $this->processContent($mail->getContent(), $mail, $trackingUrl);
        
        // Process attachments
        $processedContent = $this->processAttachments($processedContent, $mail, $trackingId);
        
        // Create the email
        $subject = $mail->getSubject();
        $imprint = $this->configRepository->getValue('imprint_mail', '');
        if($isTest) {
            $subject = "[TEST] " . $subject;
            $imprint .= "<br/><br/><strong>Dies ist eine Test E-Mail!</strong>";
        }
        
        $email = (new TemplatedEmail())
            ->replyTo(new Address($mail->getSenderEmail()))
            ->to(new Address($mail->getRecipientEmail(), $mail->getRecipientName() ?: ''))
            ->subject($subject)
            ->htmlTemplate('emails/generic.html.twig')
            ->context([
                'subject' => $mail->getSubject(),
                'headline' => str_replace(":", ":<br/>", $mail->getSubject()),
                'content' => $processedContent,
                'trackingUrl' => $trackingUrl,
                'ruestzeit' => $ruestzeit,
                'imprint' => $imprint,
            ]);
        
        // Send the email
        $this->mailer->send($email);
    }
}
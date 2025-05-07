<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Entity\MailAttachment;
use App\Entity\MailAttachmentClick;
use App\Repository\MailAttachmentClickRepository;
use App\Repository\MailAttachmentRepository;
use App\Repository\MailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Service\S3FileUploader;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MailTrackingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailRepository $mailRepository,
        private MailAttachmentRepository $mailAttachmentRepository,
        private MailAttachmentClickRepository $mailAttachmentClickRepository,
        private S3FileUploader $s3FileUploader,
        #[Autowire('%kernel.debug%')] private bool $kernelDebug
    ) {}

    #[Route('/{trackingId}/logo.png', name: 'mail_tracking_open')]
    public function trackOpen(string $trackingId): Response
    {
        $logoFile = ABSPATH . '/assets/images/logo_128.png';

        // Special case for test emails with "draft" tracking ID
        if ($trackingId === 'draft') {
            // Just return the logo without tracking for test emails
            return new BinaryFileResponse($logoFile);
        }

        try {
            $uuid = Uuid::fromString($trackingId);
            $mail = $this->mailRepository->findByUuid($trackingId);

            if (!$mail) {
                if ($this->kernelDebug) {
                    throw new Exception("Tracking ID not found");
                }

                $response = new BinaryFileResponse($logoFile);

                // you can modify headers here, before returning
                return $response;
            }

            // Update tracking information if not already opened
            if (!$mail->isOpened()) {
                $mail->setOpened(true);
                $mail->setOpenedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
            }

            $response = new BinaryFileResponse($logoFile);
            // you can modify headers here, before returning
            return $response;
        } catch (\Exception $e) {
            if ($this->kernelDebug) {
                throw $e;
            }

            $response = new BinaryFileResponse($logoFile);
            // you can modify headers here, before returning
            return $response;
        }
    }

    #[Route('/attachment/{attachmentId}/{trackingId}/{filename}', name: 'mail_tracking_attachment')]
    public function trackAttachment(string $attachmentId, string $trackingId, string $filename): Response
    {
        try {
            $attachmentUuid = Uuid::fromString($attachmentId);
            $attachment = $this->mailAttachmentRepository->findOneBy(['uuid' => $attachmentUuid]);

            if (!$attachment) {
                throw new NotFoundHttpException('Attachment not found');
            }

            // Special case for test emails with "draft" tracking ID
            if ($trackingId === 'draft') {
                // Just return the file without tracking for test emails
                return $this->outputMedia($attachment);
            }

            $mailUuid = Uuid::fromString($trackingId);
            $mail = $this->mailRepository->findOneBy(['uuid' => $mailUuid]);

            if (!$mail) {
                throw new NotFoundHttpException('Mail not found');
            }

            // Find or create a click record for this mail-attachment pair
            $click = $this->mailAttachmentClickRepository->findByMailAndAttachment(
                $mail->getId(),
                $attachment->getId()
            );

            if (!$click) {
                $click = new MailAttachmentClick();
                $click->setMail($mail);
                $click->setAttachment($attachment);
                $this->entityManager->persist($click);
            }

            // Update tracking information if not already clicked
            if (!$click->isClicked()) {
                $click->setClicked(true);
                $click->setClickedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
            }

            return $this->outputMedia($attachment);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid tracking ID or attachment ID');
        }
    }

    private function outputMedia(MailAttachment $attachment)
    {
        $s3Key = $attachment->getS3Key();
        $url = $this->s3FileUploader->getPublicUrl($s3Key);

        // Redirect to the actual file
        return $this->redirect($url);
    }
}

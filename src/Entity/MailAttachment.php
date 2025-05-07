<?php

namespace App\Entity;

use App\Entity\MailAttachmentClick;
use App\Repository\MailAttachmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MailAttachmentRepository::class)]
class MailAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $s3Key = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'mailAttachments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruestzeit $ruestzeit = null;

    #[ORM\ManyToMany(targetEntity: Mail::class, mappedBy: 'attachments')]
    private Collection $mails;

    // Global clicked flags removed - now tracked per mail in MailAttachmentClick entity

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
        $this->mails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getS3Key(): ?string
    {
        return $this->s3Key;
    }

    public function setS3Key(string $s3Key): static
    {
        $this->s3Key = $s3Key;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRuestzeit(): ?Ruestzeit
    {
        return $this->ruestzeit;
    }

    public function setRuestzeit(?Ruestzeit $ruestzeit): static
    {
        $this->ruestzeit = $ruestzeit;

        return $this;
    }

    /**
     * @return Collection<int, Mail>
     */
    public function getMails(): Collection
    {
        return $this->mails;
    }

    public function addMail(Mail $mail): static
    {
        if (!$this->mails->contains($mail)) {
            $this->mails->add($mail);
            $mail->addAttachment($this);
        }

        return $this;
    }

    public function removeMail(Mail $mail): static
    {
        if ($this->mails->removeElement($mail)) {
            $mail->removeAttachment($this);
        }

        return $this;
    }

    /**
     * Check if this attachment has been clicked by any mail
     *
     * @deprecated Use isClickedInMail() instead
     */
    public function isClicked(): bool
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $clicks = $clickRepository->findBy(['attachment' => $this, 'clicked' => true], null, 1);
        
        return !empty($clicks);
    }

    /**
     * Get the first clicked timestamp for this attachment across all mails
     *
     * @deprecated Use getClickedAtInMail() instead
     */
    public function getClickedAt(): ?\DateTimeImmutable
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $clicks = $clickRepository->findBy(['attachment' => $this, 'clicked' => true], ['clickedAt' => 'ASC'], 1);
        
        return !empty($clicks) ? $clicks[0]->getClickedAt() : null;
    }

    /**
     * Check if this attachment has been clicked in a specific email
     */
    public function isClickedInMail(Mail $mail): bool
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $click = $clickRepository->findByMailAndAttachment($mail->getId(), $this->id);
        
        return $click ? $click->isClicked() : false;
    }

    /**
     * Get the clicked timestamp for this attachment in a specific email
     */
    public function getClickedAtInMail(Mail $mail): ?\DateTimeImmutable
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $click = $clickRepository->findByMailAndAttachment($mail->getId(), $this->id);
        
        return $click ? $click->getClickedAt() : null;
    }
}
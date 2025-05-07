<?php

namespace App\Entity;

use App\Entity\MailAttachmentClick;
use App\Repository\MailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MailRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Mail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $recipientEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recipientName = null;

    #[ORM\Column(length: 255)]
    private ?string $senderEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $senderName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column]
    private bool $opened = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\ManyToOne(inversedBy: 'mails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruestzeit $ruestzeit = null;

    #[ORM\ManyToMany(targetEntity: MailAttachment::class, inversedBy: 'mails')]
    private Collection $attachments;

    #[ORM\ManyToOne]
    private ?Admin $sender = null;

    #[ORM\ManyToOne]
    private ?Anmeldung $recipient = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->sentAt = new \DateTimeImmutable();
        $this->attachments = new ArrayCollection();
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        $anmeldung = $this->recipient;
        
        if(!empty($anmeldung)) {
            $this->content = str_ireplace("(vorname)", $anmeldung->getFirstname(), $this->content);
            $this->content = str_ireplace("(nachname)", $anmeldung->getLastname(), $this->content);
        }

        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(string $recipientEmail): static
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    public function setRecipientName(?string $recipientName): static
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): static
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): static
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function isOpened(): bool
    {
        return $this->opened;
    }

    public function setOpened(bool $opened): static
    {
        $this->opened = $opened;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeImmutable $openedAt): static
    {
        $this->openedAt = $openedAt;

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
     * @return Collection<int, MailAttachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(MailAttachment $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    public function removeAttachment(MailAttachment $attachment): static
    {
        $this->attachments->removeElement($attachment);

        return $this;
    }

    public function getSender(): ?Admin
    {
        return $this->sender;
    }

    public function setSender(?Admin $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?Anmeldung
    {
        return $this->recipient;
    }

    public function setRecipient(?Anmeldung $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Check if a specific attachment has been clicked in this email
     */
    public function isAttachmentClicked(MailAttachment $attachment): bool
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $click = $clickRepository->findByMailAndAttachment($this->id, $attachment->getId());
        
        return $click ? $click->isClicked() : false;
    }

    /**
     * Get the clicked timestamp for a specific attachment in this email
     */
    public function getAttachmentClickedAt(MailAttachment $attachment): ?\DateTimeImmutable
    {
        $entityManager = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $clickRepository = $entityManager->getRepository(MailAttachmentClick::class);
        
        $click = $clickRepository->findByMailAndAttachment($this->id, $attachment->getId());
        
        return $click ? $click->getClickedAt() : null;
    }
}
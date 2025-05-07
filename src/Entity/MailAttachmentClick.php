<?php

namespace App\Entity;

use App\Repository\MailAttachmentClickRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailAttachmentClickRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_MAIL_ATTACHMENT', columns: ['mail_id', 'attachment_id'])]
class MailAttachmentClick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mail $mail = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MailAttachment $attachment = null;

    #[ORM\Column]
    private bool $clicked = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $clickedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    public function setMail(?Mail $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getAttachment(): ?MailAttachment
    {
        return $this->attachment;
    }

    public function setAttachment(?MailAttachment $attachment): static
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function isClicked(): bool
    {
        return $this->clicked;
    }

    public function setClicked(bool $clicked): static
    {
        $this->clicked = $clicked;

        return $this;
    }

    public function getClickedAt(): ?\DateTimeImmutable
    {
        return $this->clickedAt;
    }

    public function setClickedAt(?\DateTimeImmutable $clickedAt): static
    {
        $this->clickedAt = $clickedAt;

        return $this;
    }
}
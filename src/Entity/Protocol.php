<?php

namespace App\Entity;

use App\Repository\ProtocolRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Protocol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $requestData = null;

    #[ORM\ManyToOne(targetEntity: Ruestzeit::class, cascade: ["remove"])]
    private ?Ruestzeit $ruestzeit = null;

    #[ORM\ManyToOne(targetEntity: Anmeldung::class, cascade: ["remove"])]
    private ?Anmeldung $anmeldung = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $requestUri = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $requestMethod = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isSuccessful = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRequestData(): ?string
    {
        return $this->requestData;
    }

    public function setRequestData(string $requestData): static
    {
        $this->requestData = $requestData;

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

    public function getAnmeldung(): ?Anmeldung
    {
        return $this->anmeldung;
    }

    public function setAnmeldung(?Anmeldung $anmeldung): static
    {
        $this->anmeldung = $anmeldung;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): static
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $requestMethod): static
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function isIsSuccessful(): ?bool
    {
        return $this->isSuccessful;
    }

    public function setIsSuccessful(?bool $isSuccessful): static
    {
        $this->isSuccessful = $isSuccessful;

        return $this;
    }
}

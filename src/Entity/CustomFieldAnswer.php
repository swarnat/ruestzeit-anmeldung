<?php

namespace App\Entity;

use App\Repository\CustomFieldAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomFieldAnswerRepository::class)]
class CustomFieldAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomField $customField = null;

    #[ORM\ManyToOne(inversedBy: 'customFieldAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Anmeldung $anmeldung = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getCustomField(): ?CustomField
    {
        return $this->customField;
    }

    public function setCustomField(?CustomField $customField): static
    {
        $this->customField = $customField;
        return $this;
    }

    public function getAnmeldung(): ?Anmeldung
    {
        return $this->anmeldung;
    }

    public function setAnmeldung(?Anmeldung $anmeldung): static
    {
        $anmeldung->addCustomFieldAnswer($this);
        $this->anmeldung = $anmeldung;
        return $this;
    }
}

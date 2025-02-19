<?php

namespace App\Entity;

use App\Enum\CustomFieldType;
use App\Repository\CustomFieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomFieldRepository::class)]
class CustomField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', enumType: CustomFieldType::class)]
    private CustomFieldType $type;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $optional = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $intern = false;

    #[ORM\ManyToOne(inversedBy: 'customFields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Admin $owner = null;

    #[ORM\ManyToOne(inversedBy: 'customFields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruestzeit $ruestzeit = null;

    #[ORM\OneToMany(targetEntity: CustomFieldAnswer::class, mappedBy: 'customField', orphanRemoval: true)]
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getType(): CustomFieldType
    {
        return $this->type;
    }

    public function setType(CustomFieldType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function setOptional(bool $optional): static
    {
        $this->optional = $optional;
        return $this;
    }

    public function isIntern(): bool
    {
        return $this->intern;
    }

    public function setIntern(bool $intern): static
    {
        $this->intern = $intern;
        return $this;
    }

    public function getOwner(): ?Admin
    {
        return $this->owner;
    }

    public function setOwner(?Admin $owner): static
    {
        $this->owner = $owner;
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
     * @return Collection<int, CustomFieldAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(CustomFieldAnswer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setCustomField($this);
        }

        return $this;
    }

    public function removeAnswer(CustomFieldAnswer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getCustomField() === $this) {
                $answer->setCustomField(null);
            }
        }

        return $this;
    }

    public function formatValue($value) {
        switch($this->getType()) {
            case \App\Enum\CustomFieldType::CHECKBOX:
                if(is_string($value) && !empty($value)) {
                    $value = json_decode($value, true);
                }

                if(empty($value)) {
                    $value = [];
                }

                return implode(", ", $value);
                break;
            default:
                return $value;
        }
    }
}

<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $title = null;

    #[ORM\Column(length: 7)]
    private ?string $color = null;

    /**
     * @var Collection<int, Anmeldung>
     */
    #[ORM\ManyToMany(targetEntity: Anmeldung::class, mappedBy: 'categories')]
    private Collection $anmeldungen;

    public function __construct()
    {
        $this->anmeldungen = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Anmeldung>
     */
    public function getAnmeldungen(): Collection
    {
        return $this->anmeldungen;
    }

    public function addAnmeldungen(Anmeldung $anmeldungen): static
    {
        if (!$this->anmeldungen->contains($anmeldungen)) {
            $this->anmeldungen->add($anmeldungen);
            $anmeldungen->addCategory($this);
        }

        return $this;
    }

    public function removeAnmeldungen(Anmeldung $anmeldungen): static
    {
        if ($this->anmeldungen->removeElement($anmeldungen)) {
            $anmeldungen->removeCategory($this);
        }

        return $this;
    }
}

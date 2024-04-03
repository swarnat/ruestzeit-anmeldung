<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 120)]
    private ?string $street = null;

    #[ORM\Column(length: 10)]
    private ?string $postalcode = null;

    #[ORM\Column(length: 64)]
    private ?string $city = null;

    #[ORM\OneToMany(targetEntity: Ruestzeit::class, mappedBy: 'location')]
    private Collection $ruestzeiten;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Admin $user = null;

    public function __construct()
    {
        $this->ruestzeiten = new ArrayCollection();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(string $postalcode): static
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Ruestzeit>
     */
    public function getRuestzeiten(): Collection
    {
        return $this->ruestzeiten;
    }

    public function addRuestzeiten(Ruestzeit $ruestzeiten): static
    {
        if (!$this->ruestzeiten->contains($ruestzeiten)) {
            $this->ruestzeiten->add($ruestzeiten);
            $ruestzeiten->setLocation($this);
        }

        return $this;
    }

    public function removeRuestzeiten(Ruestzeit $ruestzeiten): static
    {
        if ($this->ruestzeiten->removeElement($ruestzeiten)) {
            // set the owning side to null (unless already changed)
            if ($ruestzeiten->getLocation() === $this) {
                $ruestzeiten->setLocation(null);
            }
        }

        return $this;
    }

    public function getUser(): ?Admin
    {
        return $this->user;
    }

    public function setUser(?Admin $user): static
    {
        $this->user = $user;

        return $this;
    }
}

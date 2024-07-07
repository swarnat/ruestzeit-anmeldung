<?php

namespace App\Entity;

use App\Enum\AnmeldungStatus;
use App\Repository\RuestzeitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RuestzeitRepository::class)]
#[UniqueEntity('slug')]
class Ruestzeit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $memberlimit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $registration_start = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: Anmeldung::class, mappedBy: 'ruestzeit', orphanRemoval: true)]
    private Collection $anmeldungen;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'ruestzeiten')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Admin $admin = null;

    #[ORM\ManyToOne(inversedBy: 'ruestzeiten')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_from = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_to = null;

    #[ORM\Column]
    private ?bool $show_location = true;

    #[ORM\Column]
    private ?bool $show_dates = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $internalTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $flyerUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 75, nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $registration_active = null;

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

    public function getMemberlimit(): ?int
    {
        return $this->memberlimit;
    }

    public function setMemberlimit(int $memberlimit): static
    {
        $this->memberlimit = $memberlimit;

        return $this;
    }

    public function getRegistrationStart(): ?\DateTimeInterface
    {
        return $this->registration_start;
    }

    public function setRegistrationStart(?\DateTimeInterface $registration_start): static
    {
        $this->registration_start = $registration_start;

        return $this;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Anmeldung>
     */
    public function getActiveAnmeldungen(): Collection
    {
        return $this->getAnmeldungen()->filter(function(Anmeldung $anmeldung) {
            return $anmeldung->getStatus() == AnmeldungStatus::ACTIVE;
        });
    }
    /**
     * @return Collection<int, Anmeldung>
     */
    public function getWaitlistAnmeldungen(): Collection
    {
        return $this->getAnmeldungen()->filter(function(Anmeldung $anmeldung) {
            return $anmeldung->getStatus() == AnmeldungStatus::WAITLIST;
        });
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
            $anmeldungen->setRuestzeit($this);
        }

        return $this;
    }

    public function removeAnmeldungen(Anmeldung $anmeldungen): static
    {
        if ($this->anmeldungen->removeElement($anmeldungen)) {
            // set the owning side to null (unless already changed)
            if ($anmeldungen->getRuestzeit() === $this) {
                $anmeldungen->setRuestzeit(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }

    public function getMemberCount()
    {
        return $this->getActiveAnmeldungen()->count();
    }

    public function isFull()
    {
        return $this->getActiveAnmeldungen()->count() >= $this->memberlimit;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->date_from;
    }

    public function setDateFrom(?\DateTimeInterface $date_from): static
    {
        $this->date_from = $date_from;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->date_to;
    }

    public function setDateTo(?\DateTimeInterface $date_to): static
    {
        $this->date_to = $date_to;

        return $this;
    }

    public function isShowLocation(): ?bool
    {
        return $this->show_location;
    }

    public function setShowLocation(bool $show_location): static
    {
        $this->show_location = $show_location;

        return $this;
    }

    public function isShowDates(): ?bool
    {
        return $this->show_dates;
    }

    public function setShowDates(bool $show_dates): static
    {
        $this->show_dates = $show_dates;

        return $this;
    }

    public function getInternalTitle(): ?string
    {
        return $this->internalTitle;
    }

    public function setInternalTitle(?string $internalTitle): static
    {
        $this->internalTitle = $internalTitle;

        return $this;
    }

    public function getFlyerUrl(): ?string
    {
        return $this->flyerUrl;
    }

    public function setFlyerUrl(?string $flyerUrl): static
    {
        $this->flyerUrl = $flyerUrl;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isRegistrationActive(): ?bool
    {
        return $this->registration_active;
    }

    public function setRegistrationActive(bool $registration_active): static
    {
        $this->registration_active = $registration_active;

        return $this;
    }  

}

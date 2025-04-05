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
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    #[ORM\ManyToMany(targetEntity: Admin::class)]
    #[ORM\JoinTable(name: 'ruestzeit_shared_admins')]
    private Collection $sharedAdmins;

    #[ORM\OneToMany(targetEntity: RuestzeitShareInvitation::class, mappedBy: 'ruestzeit', orphanRemoval: true)]
    private Collection $shareInvitations;

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

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $aktenzeichen = null;

    #[ORM\Column(length: 12, unique: true)]
    private ?string $forwarder = null;

    #[ORM\Column]
    private ?bool $ask_schoolclass = false;

    #[ORM\Column]
    private ?bool $showRoomRequest = false;

    #[ORM\Column]
    private ?bool $showReferer = false;

    #[ORM\Column(length: 10)]
    private ?string $admincolor = "#072f4f";

    /**
     * @var Collection<int, LanguageOverwrite>
     */
    #[ORM\OneToMany(targetEntity: LanguageOverwrite::class, mappedBy: 'ruestzeit', orphanRemoval: true)]
    private Collection $languageOverwrites;

    #[ORM\Column(nullable: true)]
    private ?bool $showRoommate = null;

    private ?UploadedFile $flyerFile = null;

    private ?UploadedFile $imageFile = null;

    #[ORM\Column(length: 48)]
    private ?string $domain = null;

    #[ORM\Column]
    private ?bool $showMealtype = false;

    #[ORM\Column(length: 255)]
    private ?string $additional_question1 = "";

    #[ORM\OneToMany(targetEntity: CustomField::class, mappedBy: 'ruestzeit', orphanRemoval: true)]
    private Collection $customFields;
    
    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'ruestzeit', orphanRemoval: true)]
    private Collection $categories;

    public function getFlyerFile(): ?UploadedFile
    {
        return $this->flyerFile;
    }

    public function setFlyerFile(?UploadedFile $flyerFile): static
    {
        $this->flyerFile = $flyerFile;
        return $this;
    }

    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function setImageFile(?UploadedFile $imageFile): static
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function __construct()
    {
        $this->anmeldungen = new ArrayCollection();
        $this->languageOverwrites = new ArrayCollection();
        $this->sharedAdmins = new ArrayCollection();
        $this->shareInvitations = new ArrayCollection();
        $this->customFields = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return "https://" . $this->getDomain() . "/" . $this->getSlug();
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

    /**
     * @return Collection<int, Admin>
     */
    public function getSharedAdmins(): Collection
    {
        return $this->sharedAdmins;
    }

    public function addSharedAdmin(Admin $admin): static
    {
        if (!$this->sharedAdmins->contains($admin)) {
            $this->sharedAdmins->add($admin);
        }

        return $this;
    }

    public function removeSharedAdmin(Admin $admin): static
    {
        $this->sharedAdmins->removeElement($admin);

        return $this;
    }

    /**
     * @return Collection<int, RuestzeitShareInvitation>
     */
    public function getShareInvitations(): Collection
    {
        return $this->shareInvitations;
    }

    public function addShareInvitation(RuestzeitShareInvitation $invitation): static
    {
        if (!$this->shareInvitations->contains($invitation)) {
            $this->shareInvitations->add($invitation);
            $invitation->setRuestzeit($this);
        }

        return $this;
    }

    public function removeShareInvitation(RuestzeitShareInvitation $invitation): static
    {
        if ($this->shareInvitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getRuestzeit() === $this) {
                $invitation->setRuestzeit(null);
            }
        }

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

    public function getAktenzeichen(): ?string
    {
        return $this->aktenzeichen;
    }

    public function setAktenzeichen(?string $aktenzeichen): static
    {
        $this->aktenzeichen = $aktenzeichen;

        return $this;
    }

    public function getForwarder(): ?string
    {
        return $this->forwarder;
    }

    public function setForwarder(string $forwarder): static
    {
        $this->forwarder = $forwarder;

        return $this;
    }

    public function isAskSchoolclass(): ?bool
    {
        return $this->ask_schoolclass;
    }

    public function setAskSchoolclass(bool $ask_schoolclass): static
    {
        $this->ask_schoolclass = $ask_schoolclass;

        return $this;
    }

    public function isShowRoomRequest(): ?bool
    {
        return $this->showRoomRequest;
    }

    public function setShowRoomRequest(bool $showRoomRequest): static
    {
        $this->showRoomRequest = $showRoomRequest;

        return $this;
    }

    public function isShowReferer(): ?bool
    {
        return $this->showReferer;
    }

    public function setShowReferer(bool $showReferer): static
    {
        $this->showReferer = $showReferer;

        return $this;
    }

    public function getAdmincolor(): ?string
    {
        return $this->admincolor;
    }

    public function setAdmincolor(string $admincolor): static
    {
        $this->admincolor = $admincolor;

        return $this;
    }

    /**
     * @return Collection<int, LanguageOverwrite>
     */
    public function getLanguageOverwrites(): Collection
    {
        return $this->languageOverwrites;
    }

    public function addLanguageOverwrite(LanguageOverwrite $languageOverwrite): static
    {
        if (!$this->languageOverwrites->contains($languageOverwrite)) {
            $this->languageOverwrites->add($languageOverwrite);
            $languageOverwrite->setRuestzeit($this);
        }

        return $this;
    }

    public function removeLanguageOverwrite(LanguageOverwrite $languageOverwrite): static
    {
        if ($this->languageOverwrites->removeElement($languageOverwrite)) {
            // set the owning side to null (unless already changed)
            if ($languageOverwrite->getRuestzeit() === $this) {
                $languageOverwrite->setRuestzeit(null);
            }
        }

        return $this;
    }

    public function isShowRoommate(): ?bool
    {
        return $this->showRoommate;
    }

    public function setShowRoommate(?bool $showRoommate): static
    {
        $this->showRoommate = $showRoommate;

        return $this;
    }

    public function hasAccessForAdmin(Admin $admin): bool
    {
        return $this->admin === $admin || $this->sharedAdmins->contains($admin);
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function isShowMealtype(): ?bool
    {
        return $this->showMealtype;
    }

    public function setShowMealtype(bool $showMealtype): static
    {
        $this->showMealtype = $showMealtype;

        return $this;
    }

    public function haveAdditionalQuestion1(): bool
    {
        return !empty($this->additional_question1);
    }

    public function getAdditionalQuestion1(): ?string
    {
        return $this->additional_question1;
    }

    public function setAdditionalQuestion1(?string $additional_question1): static
    {
        if(empty($additional_question1)) $additional_question1 = "";
        
        $this->additional_question1 = $additional_question1;

        return $this;
    }

    /**
     * @return Collection<int, CustomField>
     */
    public function getCustomFields(): Collection
    {
        return $this->customFields;
    }

    public function addCustomField(CustomField $customField): static
    {
        if (!$this->customFields->contains($customField)) {
            $this->customFields->add($customField);
            $customField->setRuestzeit($this);
        }

        return $this;
    }

    public function removeCustomField(CustomField $customField): static
    {
        if ($this->customFields->removeElement($customField)) {
            // set the owning side to null (unless already changed)
            if ($customField->getRuestzeit() === $this) {
                $customField->setRuestzeit(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setRuestzeit($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getRuestzeit() === $this) {
                $category->setRuestzeit(null);
            }
        }

        return $this;
    }
}

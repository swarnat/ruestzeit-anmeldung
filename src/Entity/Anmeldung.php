<?php

namespace App\Entity;

use App\Enum\AnmeldungStatus;
use App\Enum\MealType;
use App\Enum\PersonenTyp;
use App\Enum\RoomType;
use App\Form\AnmeldungType;
use App\Repository\AnmeldungRepository;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnmeldungRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(["ruestzeit", "firstname", "lastname", "birthdate"])]
class Anmeldung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    private ?string $firstname = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\ManyToOne(inversedBy: 'anmeldungen')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruestzeit $ruestzeit = null;

    #[ORM\Column(length: 10)]
    private ?string $postalcode = "";

    #[ORM\Column(length: 100)]
    private ?string $city = "";

    #[ORM\Column(length: 255)]
    private ?string $address = "";

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank]
    private ?string $phone = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = '';

    #[ORM\Column]
    private ?bool $prepayment_done = false;

    #[ORM\Column]
    private ?bool $payment_done = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?bool $dsgvo_agree = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?bool $agb_agree = null;

    #[ORM\Column(type: "string", enumType: MealType::class)]
    private MealType $mealtype;

    #[ORM\Column(type: "string", enumType: AnmeldungStatus::class)]
    private AnmeldungStatus $status = AnmeldungStatus::OPEN;

    #[ORM\Column]
    private ?int $registrationPosition = 0;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $landkreis = null;

    #[ORM\Column(type: "string", enumType: PersonenTyp::class)]
    private PersonenTyp $personenTyp = PersonenTyp::TEILNEHMER;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $schoolclass = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'anmeldungen', cascade: ["persist"])]
    private Collection $categories;

    #[ORM\Column(length: 18, enumType: RoomType::class, nullable: true)]
    private ?RoomType $roomRequest = null;

    #[ORM\Column(length: 255)]
    private ?string $referer = "";

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $roomnumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roommate = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }
    public function getMealtype(): MealType
    {
        return $this->mealtype;
    }

    public function setMealtype(MealType $mealtype): static
    {
        $this->mealtype = $mealtype;

        return $this;
    }

    public function getStatus(): AnmeldungStatus
    {
        return $this->status;
    }

    public function setStatus(AnmeldungStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

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

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode($postalcode): static
    {
        if(empty($postalcode)) $postalcode = "";
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity($city): static
    {
        if(empty($city)) $city = "";
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress($address): static
    {
        if(empty($address)) $address = "";

        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function isPrepaymentDone(): ?bool
    {
        return $this->prepayment_done;
    }

    public function setPrepaymentDone(bool $prepayment_done): static
    {
        $this->prepayment_done = $prepayment_done;

        return $this;
    }

    public function isPaymentDone(): ?bool
    {
        return $this->payment_done;
    }

    public function setPaymentDone(bool $payment_done): static
    {
        $this->payment_done = $payment_done;

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

    public function getAge(): int {
        $birthdate = $this->getBirthdate();

        if(empty($this->getBirthdate())) return 0;

        // $tz  = new DateTimeZone('Europe/Brussels');
   
        return $this->getBirthdate()
             ->diff($this->ruestzeit->getDateTo())
             ->y;        
    }

    public function isDsgvoAgree(): ?bool
    {
        return $this->dsgvo_agree;
    }

    public function setDsgvoAgree(bool $dsgvo_agree): static
    {
        $this->dsgvo_agree = $dsgvo_agree;

        return $this;
    }

    public function isAgbAgree(): ?bool
    {
        return $this->agb_agree;
    }

    public function setAgbAgree(bool $agb_agree): static
    {
        $this->agb_agree = $agb_agree;

        return $this;
    }

    public function getRegistrationPosition(): ?int
    {
        return $this->registrationPosition;
    }

    public function setRegistrationPosition(int $registrationPosition): static
    {
        $this->registrationPosition = $registrationPosition;

        return $this;
    }

    public function getLandkreis(): ?string
    {
        return $this->landkreis;
    }

    public function setLandkreis(?string $landkreis): static
    {
        $this->landkreis = $landkreis;

        return $this;
    }

    public function getPersonenTyp(): PersonenTyp
    {
        return $this->personenTyp;
    }

    public function setPersonenTyp(PersonenTyp $personenTyp): static
    {
        $this->personenTyp = $personenTyp;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSchoolclass(): ?string
    {
        return $this->schoolclass;
    }

    public function setSchoolclass(?string $schoolclass): static
    {
        $this->schoolclass = $schoolclass;

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
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function haveCategory(Category $category): bool
    {
        return $this->getCategories()->indexOf($category) !== false;
    }

    public function getRoomRequest(): ?RoomType
    {
        return $this->roomRequest;
    }

    public function setRoomRequest(RoomType $roomRequest): static
    {
        $this->roomRequest = $roomRequest;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): static
    {
        $this->referer = $referer;

        return $this;
    }

    public function getRoomnumber(): ?string
    {
        return $this->roomnumber;
    }

    public function setRoomnumber(?string $roomnumber): static
    {
        $this->roomnumber = $roomnumber;

        return $this;
    }

    public function getRoommate(): ?string
    {
        return $this->roommate;
    }

    public function setRoommate(?string $roommate): static
    {
        $this->roommate = $roommate;

        return $this;
    }
}

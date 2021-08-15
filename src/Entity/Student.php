<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StudentRepository;
use App\Traits\AuthorEntityTrait;
use App\Traits\SchoolEntityTrait;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=StudentRepository::class)
 */
class Student
{
    use IdEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use SchoolEntityTrait;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, inversedBy="student", cascade={"persist"})
     */
    private ?Person $person = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastSchool = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $personAuthorized = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $remarksHealth = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $letAlone = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $dateRegistration = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $dateDesactivated = null;

    /**
     * @ORM\ManyToOne(targetEntity=Grade::class, cascade={"persist"})
     */
    private ?Grade $grade = null;

    /**
     * @var Collection|ClassPeriodStudent[]
     * @ORM\OneToMany(targetEntity=ClassPeriodStudent::class, mappedBy="student", cascade={"persist"})
     */
    private array|Collection $classPeriods;

    /**
     * @var Collection|PackageStudentPeriod[]
     * @ORM\OneToMany(targetEntity=PackageStudentPeriod::class, mappedBy="student", cascade={"persist"})
     */
    private array|Collection $packagePeriods;

    /**
     * @var Collection|AppealCourse[]
     * @ORM\OneToMany(targetEntity=AppealCourse::class, mappedBy="student")
     */
    private array|Collection $courses;

    /**
     * @var Collection|StudentComment[]
     * @ORM\OneToMany(targetEntity=StudentComment::class, mappedBy="student")
     */
    private array|Collection $comments;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->setPerson(new Person())
            ->setEnable(true)
            ->setDateRegistration(new DateTime());

        $this->classPeriods = new ArrayCollection();
        $this->packagePeriods = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @throws Exception
     */
    public function setEnable(bool $enable) : static
    {
        $this->enable = $enable;

        $this->setDateDesactivated(null);

        if (!$this->enable) {
            $this->setDateDesactivated(new DateTime());
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getNameComplete();
    }

    public function getNameComplete(): ?string
    {
        return $this->person?->getNameComplete();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->person?->getName();
    }


    public function setGender(string $gender) : static
    {
        $this->person->setGender($gender);

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->person->getGender();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getGenderCode()
    {
        return $this->person->getGender();
    }

    /**
     * Set forname.
     *
     * @return Student
     */
    public function setForname(string $forname)
    {
        $this->person->setForname($forname);

        return $this;
    }

    public function getForname() : string
    {
        return $this->person->getForname();
    }

    public function setBirthday(DateTimeInterface $birthday) : static
    {
        $this->person->setBirthday($birthday);

        return $this;
    }

    public function getBirthday() : ?DateTimeInterface
    {
        return $this->person?->getBirthday();
    }

    public function setBirthplace(string $birthplace) : static
    {
        $this->person->setBirthplace($birthplace);

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->person?->getBirthplace();
    }

    public function setAddress(string $address) : static
    {
        $this->person?->setAddress($address);

        return $this;
    }

    public function getAddress() : ?string
    {
        return $this->person?->getAddress() ?? $this->person?->getFamily()?->getAddress();
    }

    public function getPerson() : ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function setPostcode(string $postcode): self
    {
        $this->person->setZip($postcode);

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->getZip();
    }

    /**
     * Get zip.
     */
    public function getZip(): ?string
    {
        $zip = $this->person->getZip();
        if (empty($zip) && !empty($this->person->getFamily())) {
            $zip = $this->person->getFamily()->getZip();
        }

        return $zip;
    }

    public function setTown(string $town): self
    {
        $this->person->setCity($town);

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->getCity();
    }

    public function getCity(): ?string
    {
        $city = $this->person->getCity();

        if (empty($city) && !empty($this->person->getFamily())) {
            $city = $this->person->getFamily()->getCity();
        }

        return $city;
    }

    /**
     * Set phone.
     *
     * @param bool $add
     *
     * @return self
     */
    public function setPhone(string $phone, $add = false)
    {
        $this->person->setPhone($phone, $add);

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->person->getPhone();
    }

    /**
     * Add number phone.
     *
     * @param $phone
     *
     * @return self
     *
     * @throws Exception
     */
    public function addPhone($phone)
    {
        $this->person->addPhone($phone);

        return $this;
    }

    /**
     * Add number phone.
     *
     * @param $key
     *
     * @throws Exception
     */
    public function removePhone(string $key): self
    {
        $this->person->removePhone($key);

        return $this;
    }

    public function getListPhones(): array
    {
        return array_unique(array_merge(
            $this->person?->getListPhones() ?? [],
            $this->getFamily()?->getMother()?->getListPhones() ?? [],
            $this->getFamily()?->getFather()?->getListPhones() ?? [],
            $this->getFamily()?->getLegalGuardian()?->getListPhones() ?? [],
        ));
    }

    public function getFamily(): ?Family
    {
        return $this->person?->getFamily();
    }

    public function setEmail(string $email) : static
    {
        $this->person->setEmail($email);

        return $this;
    }

    public function getEmail() : ?string
    {
        return $this->person->getEmail();
    }

    public function getLastSchool() : ?string
    {
        return $this->lastSchool;
    }

    public function setLastSchool(string $lastSchool): self
    {
        $this->lastSchool = $lastSchool;

        return $this;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(Grade $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function setImage(Document $image): self
    {
        $this->person->setImage($image);

        return $this;
    }

    public function getImage(): ?Document
    {
        return $this->person->getImage();
    }

    public function getStatusLabel(): string
    {
        return $this->enable ? 'Actif' : 'Inactif';
    }

    public function getAge(): ?int
    {
        return $this->person->getAge();
    }

    public function addClassPeriod(ClassPeriodStudent $classPeriod): self
    {
        $this->classPeriods[] = $classPeriod;

        return $this;
    }

    /**
     * Remove classPeriods.
     */
    public function removeClassPeriod(ClassPeriodStudent $classPeriod): void
    {
        $this->classPeriods->removeElement($classPeriod);
    }

    public function getClassPeriods() : Collection
    {
        return $this->classPeriods;
    }

    public function getClassToPeriod(Period $period) : ?ClassPeriodStudent
    {
        $current = null;

        /* @var $classes ClassPeriodStudent[] */
        $classes = $this->classPeriods->toArray();
        foreach ($classes as $classPeriod) {
            if (!$classPeriod->getClassPeriod() instanceof ClassPeriod) {
                continue;
            }

            if ($classPeriod->getClassPeriod()->getPeriod()->getId() === $period->getId()) {
                $current = $classPeriod;
            }
        }

        return $current;
    }

    public function addPackagePeriod(PackageStudentPeriod $packagePeriods) : static
    {
        $this->packagePeriods[] = $packagePeriods;

        return $this;
    }

    public function removePackagePeriod(PackageStudentPeriod $packagePeriods): void
    {
        $this->packagePeriods->removeElement($packagePeriods);
    }

    public function getPackagePeriods() : Collection
    {
        return $this->packagePeriods;
    }

    public function getDateDesactivated() : ?DateTimeInterface
    {
        if (!$this->enable && empty($this->dateDesactivated)) {
            $this->dateDesactivated = new DateTime();
        }

        return $this->dateDesactivated;
    }

    public function setDateDesactivated(DateTimeInterface $dateDesactivated = null) : static
    {
        $this->dateDesactivated = $dateDesactivated;

        return $this;
    }


    public function addCourse(AppealCourse $courses) : static
    {
        $this->courses[] = $courses;

        return $this;
    }

    public function removeCourse(AppealCourse $courses): void
    {
        $this->courses?->removeElement($courses);
    }

    /**
     * @return Collection|AppealCourse[]
     */
    public function getCourses() : Collection
    {
        return $this->courses;
    }

    public function getDateRegistration() : ?DateTimeInterface
    {
        return $this->dateRegistration;
    }

    public function setDateRegistration(DateTimeInterface $dateRegistration = null) : static
    {
        $this->dateRegistration = $dateRegistration;

        return $this;
    }

    public function addComment(StudentComment $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    public function removeComment(StudentComment $comment): void
    {
        $this->comments?->removeElement($comment);
    }

    public function getComments() : Collection
    {
        return $this->comments;
    }

    public function getPersonAuthorized() : ?string
    {
        return $this->personAuthorized;
    }

    public function setPersonAuthorized(string $personAuthorized = null) : static
    {
        $this->personAuthorized = $personAuthorized;

        return $this;
    }

    public function getRemarksHealth(): ?string
    {
        return $this->remarksHealth;
    }

    public function setRemarksHealth(string $remarksHealth = null): self
    {
        $this->remarksHealth = $remarksHealth;

        return $this;
    }

    public function getLetAlone(): bool
    {
        return $this->letAlone;
    }

    public function setLetAlone(bool $letAlone): self
    {
        $this->letAlone = $letAlone;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function since(): ?int
    {
        return $this->createdAt?->diff(new DateTime())->y;
    }
}

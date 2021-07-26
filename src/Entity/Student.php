<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\AuthorEntityTrait;
use App\Traits\SchoolEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\StudentRepository")
 */
class Student
{
    use IdEntity;
    use AuthorEntityTrait;
    use EnableEntity;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use SchoolEntityTrait;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, inversedBy="student", cascade={"persist"})
     */
    private ?\App\Entity\Person $person = null;

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
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $letAlone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $dateRegistration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDesactivated;

    /**
     * @ORM\ManyToOne(targetEntity=Grade::class, cascade={"persist"})
     */
    private ?\App\Entity\Grade $grade = null;

    /**
     * @ORM\OneToMany(targetEntity=ClassPeriodStudent::class, mappedBy="student", cascade={"persist"})
     */
    private array|\Doctrine\Common\Collections\Collection|\Doctrine\Common\Collections\ArrayCollection $classPeriods;

    /**
     * @ORM\OneToMany(targetEntity=PackageStudentPeriod::class, mappedBy="student", cascade={"persist"})
     */
    private array|\Doctrine\Common\Collections\Collection|\Doctrine\Common\Collections\ArrayCollection $packagePeriods;

    /**
     * @ORM\OneToMany(targetEntity=AppealCourse::class, mappedBy="student")
     */
    private array|\Doctrine\Common\Collections\Collection|\Doctrine\Common\Collections\ArrayCollection $courses;

    /**
     * @ORM\OneToMany(targetEntity=StudentComment::class, mappedBy="student")
     */
    private array|\Doctrine\Common\Collections\Collection|\Doctrine\Common\Collections\ArrayCollection $comments;

    /**
     * Constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPerson(new Person())
            ->setLetAlone(false)
            ->setEnable(true)
            ->setDateRegistration(new \DateTime());

        $this->classPeriods = new ArrayCollection();
        $this->packagePeriods = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Set status.
     *
     * @return Student
     *
     * @throws \Exception
     */
    public function setEnable(bool $enable)
    {
        $this->enable = $enable;

        $this->setDateDesactivated(null);

        if (!$this->enable) {
            $this->setDateDesactivated(new \DateTime());
        }

        return $this;
    }

    /**
     * toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getNameComplete();
    }

    /**
     * Get name complete.
     *
     * @return string
     */
    public function getNameComplete()
    {
        return $this->person->getNameComplete();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name complete.
     *
     * @return string
     */
    public function getName()
    {
        return $this->person->getName();
    }

    /**
     * Set name.
     *
     * @return Student
     */
    public function setGender(string $gender)
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

    /**
     * Get forname.
     *
     * @return string
     */
    public function getForname()
    {
        return $this->person->getForname();
    }

    /**
     * Set birthday.
     *
     * @return Student
     * @param \DateTime|\DateTimeImmutable $birthday
     */
    public function setBirthday(\DateTimeInterface $birthday)
    {
        $this->person->setBirthday($birthday);

        return $this;
    }

    /**
     * Get birthday.
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->person->getBirthday();
    }

    /**
     * Set birthplace.
     *
     * @return Student
     */
    public function setBirthplace(string $birthplace)
    {
        $this->person->setBirthplace($birthplace);

        return $this;
    }

    public function getBirthplace() : ?string
    {
        return $this->person->getBirthplace();
    }

    /**
     * Set address.
     *
     * @return Student
     */
    public function setAddress(string $address)
    {
        $this->person->setAddress($address);

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        $adress = $this->person->getAddress();

        if (empty($adress) && !empty($this->getPerson()->getFamily())) {
            $adress = $this->getPerson()->getFamily()->getAddress();
        }

        return $adress;
    }

    /**
     * Get person.
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson(Person $person) : self
    {
        $this->person = $person;
        return $this;
    }

    public function setPostcode(string $postcode) : self
    {
        $this->person->setZip($postcode);
        return $this;
    }

    public function getPostcode() : ?string
    {
        return $this->getZip();
    }

    public function setTown(string $town) : self
    {
        $this->person->setCity($town);
        return $this;
    }

    public function getTown() : ?string
    {
        return $this->getCity();
    }

    public function getCity() : ?string
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
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function removePhone(string $key): self
    {
        $this->person->removePhone($key);

        return $this;
    }

    /**
     * Get phone.
     */
    public function getListPhones(): array
    {
        $phones = $this->person->getListPhones();
        if (!empty($this->getFamily())) {
            if (!empty($this->getFamily()->getMother())) {
                $phones = array_merge($phones, $this->getFamily()->getMother()->getListPhones());
            }

            if (!empty($this->getFamily()->getFather())) {
                $phones = array_merge($phones, $this->getFamily()->getFather()->getListPhones());
            }

            if (!empty($this->getFamily()->getLegalGuardian())) {
                $phones = array_merge($phones, $this->getFamily()->getLegalGuardian()->getListPhones());
            }
        }

        return array_unique(array_values($phones));
    }

    /**
     * Set email.
     *
     * @return Student
     */
    public function setEmail(string $email)
    {
        $this->person->setEmail($email);

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->person->getEmail();
    }

    /**
     * Get lastSchool.
     *
     * @return string
     */
    public function getLastSchool()
    {
        return $this->lastSchool;
    }

    public function setLastSchool(string $lastSchool) : self
    {
        $this->lastSchool = $lastSchool;
        return $this;
    }

    public function getGrade() : ?Grade
    {
        return $this->grade;
    }

    public function setGrade(Grade $grade) : self
    {
        $this->grade = $grade;
        return $this;
    }

    public function setImage(Document $image) : self
    {
        $this->person->setImage($image);
        return $this;
    }

    public function getImage() : ?Document
    {
        return $this->person->getImage();
    }

    public function getStatusLabel() : string
    {
        return $this->enable ? 'Actif' : 'Inactif';
    }

    public function getAge() : ?int
    {
        return $this->person->getAge();
    }

    public function addClassPeriod(ClassPeriodStudent $classPeriod) :self
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

    /**
     * Get classPeriods.
     *
     * @return Collection
     */
    public function getClassPeriods()
    {
        return $this->classPeriods;
    }

    /**
     * Get classPeriods.
     *
     * @return ClassPeriodStudent|null
     */
    public function getClassToPeriod(Period $period)
    {
        $current = null;

        /* @var $classPeriod ClassPeriodStudent */
        foreach ($this->classPeriods->toArray() as $classPeriod) {
            if (!$classPeriod->getClassPeriod() instanceof ClassPeriod) {
                continue;
            }

            if ($classPeriod->getClassPeriod()->getPeriod()->getId() === $period->getId()) {
                $current = $classPeriod;
            }
        }

        return $current;
    }

    /**
     * Add packagePeriods.
     *
     * @return Student
     */
    public function addPackagePeriod(PackageStudentPeriod $packagePeriods)
    {
        $this->packagePeriods[] = $packagePeriods;

        return $this;
    }

    /**
     * Remove packagePeriods.
     */
    public function removePackagePeriod(PackageStudentPeriod $packagePeriods): void
    {
        $this->packagePeriods->removeElement($packagePeriods);
    }

    /**
     * Get packagePeriods.
     *
     * @return Collection|ArrayCollection
     */
    public function getPackagePeriods()
    {
        return $this->packagePeriods;
    }

    /**
     * Get dateDeactivated.
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    public function getDateDesactivated()
    {
        if (!$this->isEnable() && empty($this->dateDesactivated)) {
            $this->dateDesactivated = new \DateTime();
        }

        return $this->dateDesactivated;
    }

    /**
     * Set dateDesactivated.
     *
     *
     * @return Student
     * @param \DateTime|\DateTimeImmutable $dateDesactivated
     */
    public function setDateDesactivated(\DateTimeInterface $dateDesactivated = null)
    {
        $this->dateDesactivated = $dateDesactivated;

        return $this;
    }

    /**
     * Add courses.
     *
     * @return Student
     */
    public function addCourse(AppealCourse $courses)
    {
        $this->courses[] = $courses;

        return $this;
    }

    /**
     * Remove courses.
     */
    public function removeCourse(AppealCourse $courses): void
    {
        $this->courses->removeElement($courses);
    }

    /**
     * Get courses.
     *
     * @return Collection|AppealCourse[]
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * Get dateRegistration.
     *
     * @return \DateTime
     */
    public function getDateRegistration()
    {
        return $this->dateRegistration;
    }

    /**
     * Set dateRegistration.
     *
     *
     * @return Student
     * @param \DateTime|\DateTimeImmutable $dateRegistration
     */
    public function setDateRegistration(\DateTimeInterface $dateRegistration = null)
    {
        $this->dateRegistration = $dateRegistration;

        return $this;
    }

    public function addComment(StudentComment $comment) : self
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * Remove comment.
     */
    public function removeComment(StudentComment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments.
     *
     * @return Collection|StudentComment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Get personAuthorized.
     *
     * @return string|null
     */
    public function getPersonAuthorized()
    {
        return $this->personAuthorized;
    }

    /**
     * Set personAuthorized.
     *
     * @return Student
     */
    public function setPersonAuthorized(string $personAuthorized = null)
    {
        $this->personAuthorized = $personAuthorized;
        return $this;
    }

    /**
     * Get remarksHealth.
     */
    public function getRemarksHealth(): ?string
    {
        return $this->remarksHealth;
    }

    /**
     * Set remarksHealth.
     */
    public function setRemarksHealth(string $remarksHealth = null): self
    {
        $this->remarksHealth = $remarksHealth;

        return $this;
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

    /**
     * Get letAlone.
     */
    public function getLetAlone(): bool
    {
        return $this->letAlone;
    }

    /**
     * Set letAlone.
     */
    public function setLetAlone(bool $letAlone): self
    {
        $this->letAlone = $letAlone;

        return $this;
    }

    /**
     * Get letAlone.
     *
     * @return Family
     */
    public function getFamily(): ?Family
    {
        return $this->person->getFamily();
    }

    /**
     * @throws \Exception
     */
    public function since(): int
    {
        $since = null;
        $since = $this->createdAt->diff(new \DateTime())->y;

        return $since;
    }
}

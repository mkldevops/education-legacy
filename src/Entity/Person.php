<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Manager\PhoneManager;
use App\Repository\PersonRepository;
use App\Traits\AuthorEntityTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\AddressEntityTrait;
use Fardus\Traits\Symfony\Entity\CityEntityTrait;
use Fardus\Traits\Symfony\Entity\EmailEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\ZipEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    use IdEntityTrait;
    use NameEntityTrait;
    use EmailEntityTrait;
    use AddressEntityTrait;
    use ZipEntityTrait;
    use CityEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string")
     */
    protected ?string $forname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $phone = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $birthday = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $birthplace;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected ?string $gender;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?User $user;

    /**
     * @ORM\OneToOne(targetEntity=Member::class, cascade={"persist"}, mappedBy="person")
     */
    protected ?Member $member;

    /**
     * @ORM\OneToOne(targetEntity=Student::class, cascade={"persist"}, mappedBy="person")
     */
    protected ?Student $student;

    /**
     * @ORM\ManyToMany(targetEntity=School::class, cascade={"persist", "merge", "remove"})
     */
    protected Collection $schools;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class, inversedBy="persons", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    protected ?Document $image = null;

    /**
     * @ORM\ManyToOne(targetEntity=Family::class, inversedBy="persons", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    protected ?Family $family;

    public function __construct()
    {
        $this->setSchools(new ArrayCollection())
            ->setEnable(true);
    }

    public function __toString(): string
    {
        return $this->getNameComplete();
    }

    public function getNameComplete(): string
    {
        return strtoupper($this->name) . ' ' . ucwords($this->forname);
    }

    public function getAge(): ?int
    {
        $age = null;
        if ($this->birthday instanceof DateTimeInterface) {
            $age = $this->birthday->diff(new DateTime())->y;
        }

        return $age;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForname(): ?string
    {
        return $this->forname;
    }

    public function setForname(string $forname): self
    {
        $this->forname = $forname;

        return $this;
    }

    /**
     * @throws AppException
     */
    public function addPhone(string $phone): self
    {
        if (empty($phone)) {
            throw new AppException('The phone number is not defined');
        }

        $this->setPhone($phone, true);

        return $this;
    }

    /**
     * @throws AppException
     */
    public function removePhone(string $key): self
    {
        if (empty($key)) {
            throw new AppException('The key phone number is not defined');
        }

        $list = $this->getListPhones();

        if (array_key_exists($key, $list)) {
            unset($list[$key]);
        } else {
            throw new AppException('The key phone number is undefined to list');
        }

        return $this->setPhone(implode(';', $list));
    }

    public function getListPhones(): array
    {
        return PhoneManager::stringPhonesToArray($this->getPhone());
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone = null, bool $add = false): self
    {
        // Check if number phone is str
        if (!empty($phone) && $add && !empty($this->phone)) {
            $phone = $this->phone . ';' . $phone;
        }

        $phone = PhoneManager::stringPhonesToArray($phone);
        $this->phone = implode(';', $phone);

        return $this;
    }

    public function getAllPhones(): array
    {
        return PhoneManager::getAllPhones($this);
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday = null): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): self
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function hasGender(): bool
    {
        return !empty($this->getGender());
    }

    public function getGender(): ?string
    {
        $gender = $this->gender;

        if (in_array($gender, ['feminin', 'masculin'], true)) {
            $gender = 'masculin' === $gender ? Person::GENDER_MALE : self::GENDER_FEMALE;
        }

        return $gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function setSchools(Collection $schools): self
    {
        $this->schools = $schools;

        return $this;
    }

    public function addSchool(School $school): self
    {
        $this->schools[] = $school;

        return $this;
    }

    public function removeSchool(School $school): self
    {
        $this->schools->removeElement($school);

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getImage(): ?Document
    {
        return $this->image;
    }

    public function setImage(?Document $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getFamily(): ?Family
    {
        return $this->family;
    }

    public function setFamily(?Family $family): self
    {
        $this->family = $family;

        return $this;
    }
}

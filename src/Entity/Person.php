<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Exception\AppException;
use App\Manager\PhoneManager;
use App\Repository\PersonRepository;
use App\Trait\AddressEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CityEntityTrait;
use App\Trait\EmailEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\ZipEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person implements \Stringable, EntityInterface, AuthorEntityInterface
{
    use AddressEntityTrait;
    use AuthorEntityTrait;
    use CityEntityTrait;
    use EmailEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;
    use ZipEntityTrait;
    /**
     * @var string
     */
    final public const GENDER_MALE = 'male';

    /**
     * @var string
     */
    final public const GENDER_FEMALE = 'female';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    protected ?string $forname = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $birthday = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $birthplace = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    protected ?string $gender = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?User $user = null;

    #[ORM\OneToOne(targetEntity: Member::class, cascade: ['persist'], mappedBy: 'person')]
    protected ?Member $member = null;

    #[ORM\OneToOne(targetEntity: Student::class, cascade: ['persist', 'remove'], mappedBy: 'person', orphanRemoval: true)]
    protected ?Student $student = null;

    /**
     * @var Collection<int, School>
     */
    #[ORM\ManyToMany(targetEntity: School::class, cascade: ['persist', 'merge', 'remove'])]
    protected Collection $schools;

    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'persons', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Valid]
    protected ?Document $image = null;

    #[ORM\ManyToOne(targetEntity: Family::class, inversedBy: 'persons', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Valid]
    protected ?Family $family = null;

    public function __construct()
    {
        $this->schools = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getNameComplete();
    }

    public function getNameComplete(): string
    {
        return \sprintf('%s %s', strtoupper((string) $this->name), ucwords((string) $this->forname));
    }

    public function getAge(): ?int
    {
        if ($this->birthday instanceof \DateTimeInterface) {
            return $this->birthday->diff(new \DateTime())->y;
        }

        return null;
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
        if ('' === $phone || '0' === $phone) {
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
        if ('' === $key || '0' === $key) {
            throw new AppException('The key phone number is not defined');
        }

        $list = $this->getListPhones();

        if (\array_key_exists($key, $list)) {
            unset($list[$key]);
        } else {
            throw new AppException('The key phone number is undefined to list');
        }

        return $this->setPhone(implode(';', $list));
    }

    /**
     * @return string[]
     */
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
        if (null !== $phone && '' !== $phone && '0' !== $phone && $add && (null !== $this->phone && '' !== $this->phone && '0' !== $this->phone)) {
            $phone = $this->phone.';'.$phone;
        }

        $phone = PhoneManager::stringPhonesToArray($phone);
        $this->phone = implode(';', $phone);

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getAllPhones(): array
    {
        return PhoneManager::getAllPhones($this);
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday = null): self
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
        return !\in_array($this->getGender(), [null, '', '0'], true);
    }

    public function getGender(): ?string
    {
        $gender = $this->gender;

        if (\in_array($gender, ['feminin', 'masculin'], true)) {
            return 'masculin' === $gender ? self::GENDER_MALE : self::GENDER_FEMALE;
        }

        return $gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection<int, School>
     */
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

    public function setImage(?Document $document): self
    {
        $this->image = $document;

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

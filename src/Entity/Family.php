<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\FamilyRepository;
use App\Trait\AddressEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CityEntityTrait;
use App\Trait\EmailEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\ZipEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Accessors\NameAccessorsTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Family implements EntityInterface, AuthorEntityInterface
{
    use AddressEntityTrait;
    use AuthorEntityTrait;
    use CityEntityTrait;
    use EmailEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameAccessorsTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;
    use ZipEntityTrait;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $name = null;

    #[ORM\OneToOne(targetEntity: Person::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(unique: true)]
    private ?Person $father = null;

    #[ORM\OneToOne(targetEntity: Person::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(unique: true)]
    private ?Person $mother = null;

    #[ORM\OneToOne(targetEntity: Person::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(unique: true)]
    private ?Person $legalGuardian = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $language = null;

    #[Assert\Range(min: 1, max: 12)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $numberChildren = 1;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $personAuthorized = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $personEmergency = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(mappedBy: 'family', targetEntity: Person::class, cascade: ['remove', 'persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $persons;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf('%d - %s', (int) $this->getId(), $this->getNameComplete());
    }

    public function setGenders(): self
    {
        $this->mother?->setGender(Person::GENDER_FEMALE);
        $this->father?->setGender(Person::GENDER_MALE);
        $this->legalGuardian?->setGender(Person::GENDER_MALE);

        return $this;
    }

    public function getNameComplete(): string
    {
        $persons = [];
        if ($this->mother instanceof Person) {
            $persons[] = (string) $this->mother;
        }

        if ($this->father instanceof Person) {
            $persons[] = (string) $this->father;
        }

        if ($this->legalGuardian instanceof Person && \count($persons) < 2) {
            $persons[] = (string) $this->getLegalGuardian();
        }

        if (\count($persons) < 2 && !empty($this->persons->get(0))) {
            $persons[] = \sprintf('[%s]', (string) $this->persons->first());
        }

        if (\count($persons) < 2 && !empty($this->persons->get(1))) {
            $persons[] = \sprintf('[%s]', (string) $this->persons->get(1));
        }

        if (\count($this->getPersons()) > 2) {
            $persons[] = '...';
        }

        return implode(', ', $persons);
    }

    public function getLegalGuardian(): ?Person
    {
        return $this->legalGuardian;
    }

    public function setLegalGuardian(?Person $person = null): self
    {
        $this->legalGuardian = $person;

        if ($this->legalGuardian instanceof Person && !$this->legalGuardian->hasGender()) {
            $this->legalGuardian->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    public function getPersons(): Collection
    {
        return $this->persons;
    }

    #[ORM\PrePersist]
    public function setNameValue(): void
    {
        $this->name = $this->getNameComplete();
    }

    public function getFather(): ?Person
    {
        return $this->father;
    }

    public function setFather(?Person $person = null): self
    {
        $this->father = $person;

        if ($this->father instanceof Person && !$this->father->hasGender()) {
            $this->father->setGender(Person::GENDER_MALE);
        }

        return $this;
    }

    public function getMother(): ?Person
    {
        return $this->mother;
    }

    public function setMother(?Person $person = null): static
    {
        $this->mother = $person;

        if ($this->mother instanceof Person && !$this->mother->hasGender()) {
            $this->mother->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language = null): static
    {
        $this->language = $language;

        return $this;
    }

    public function getNumberChildren(): int
    {
        return $this->numberChildren;
    }

    public function setNumberChildren(int $numberChildren): static
    {
        $this->numberChildren = $numberChildren;

        return $this;
    }

    public function getPersonAuthorized(): ?string
    {
        return $this->personAuthorized;
    }

    public function setPersonAuthorized(?string $personAuthorized = null): static
    {
        $this->personAuthorized = $personAuthorized;

        return $this;
    }

    public function getPersonEmergency(): ?string
    {
        return $this->personEmergency;
    }

    public function setPersonEmergency(?string $personEmergency = null): static
    {
        $this->personEmergency = $personEmergency;

        return $this;
    }

    public function addPerson(Person $person): static
    {
        $this->persons[] = $person;

        return $this;
    }

    public function removePerson(Person $person): bool
    {
        return $this->persons->removeElement($person);
    }
}

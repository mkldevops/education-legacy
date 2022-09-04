<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FamilyRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Accessors\NameAccessorsTrait;
use Fardus\Traits\Symfony\Entity\AddressEntityTrait;
use Fardus\Traits\Symfony\Entity\CityEntityTrait;
use Fardus\Traits\Symfony\Entity\EmailEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\ZipEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FamilyRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Family
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

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $name = null;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, cascade={"persist"})
     * @ORM\JoinColumn(unique=true)
     */
    private ?Person $father = null;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, cascade={"persist"})
     * @ORM\JoinColumn(unique=true)
     */
    private ?Person $mother = null;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, cascade={"persist"})
     * @ORM\JoinColumn(unique=true)
     */
    private ?Person $legalGuardian = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $language = null;

    /**
     * @Assert\Range(min="1", max="12")
     * @ORM\Column(type="integer")
     */
    private int $numberChildren = 1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $personAuthorized;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $personEmergency;

    /**
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="family", cascade={"remove"})
     */
    private Collection $persons;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%d - %s', (int) $this->getId(), $this->getNameComplete());
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
        if (null !== $this->mother) {
            $persons[] = (string) $this->mother;
        }

        if (null !== $this->father) {
            $persons[] = (string) $this->father;
        }

        if (null !== $this->legalGuardian && \count($persons) < 2) {
            $persons[] = (string) $this->getLegalGuardian();
        }

        if (!empty($this->persons)) {
            if (\count($persons) < 2 && !empty($this->persons->get(0))) {
                $persons[] = sprintf('[%s]', (string) $this->persons->first());
            }

            if (\count($persons) < 2 && !empty($this->persons->get(1))) {
                $persons[] = sprintf('[%s]', (string) $this->persons->get(1));
            }

            if (\count($this->getPersons()) > 2) {
                $persons[] = '...';
            }
        }

        return implode(', ', $persons);
    }

    public function getLegalGuardian(): ?Person
    {
        return $this->legalGuardian;
    }

    public function setLegalGuardian(Person $legalGuardian = null): self
    {
        $this->legalGuardian = $legalGuardian;

        if (null !== $this->legalGuardian && !$this->legalGuardian->hasGender()) {
            $this->legalGuardian->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    public function getPersons(): Collection
    {
        return $this->persons;
    }

    /**
     * @ORM\PrePersist
     */
    public function setNameValue(): void
    {
        $this->name = $this->getNameComplete();
    }

    public function getFather(): ?Person
    {
        return $this->father;
    }

    public function setFather(Person $father = null): self
    {
        $this->father = $father;

        if (null !== $this->father && !$this->father->hasGender()) {
            $this->father->setGender(Person::GENDER_MALE);
        }

        return $this;
    }

    public function getMother(): ?Person
    {
        return $this->mother;
    }

    public function setMother(Person $mother = null): static
    {
        $this->mother = $mother;

        if (null !== $this->mother && !$this->mother->hasGender()) {
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

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FamilyRepository;
use App\Traits\AuthorEntityTrait;
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
 * @ORM\Entity(repositoryClass=FamilyRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Family
{
    use IdEntityTrait;
    use NameEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use EmailEntityTrait;
    use AddressEntityTrait;
    use CityEntityTrait;
    use ZipEntityTrait;

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
        $this->setNumberChildren(1)
            ->setEnable(false)
            ->persons = new ArrayCollection();
    }

    public function setGenders(): self
    {
        $this->mother?->setGender(Person::GENDER_FEMALE);
        $this->father?->setGender(Person::GENDER_MALE);
        $this->legalGuardian?->setGender(Person::GENDER_MALE);

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%d - %s', $this->getId(), $this->getNameComplete());
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getNameComplete(): string
    {
        $persons = [];
        if (null !== $this->mother) {
            $persons[] = (string)$this->mother;
        }

        if (null !== $this->father) {
            $persons[] = (string)$this->father;
        }

        if (count($persons) < 2 && null !== $this->legalGuardian) {
            $persons[] = (string)$this->getLegalGuardian();
        }

        if (!empty($this->persons)) {
            if (count($persons) < 2 && !empty($this->persons?->get(0))) {
                $persons[] = sprintf('[%s]', (string)$this->persons?->first());
            }

            if (count($persons) < 2 && !empty($this->getPersons()->get(1))) {
                $persons[] = sprintf('[%s]', (string)$this->getPersons()->get(1));
            }

            if (count($this->getPersons()) > 2) {
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

        if (!$this->legalGuardian->hasGender()) {
            $this->legalGuardian->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    /**
     * Get persons.
     *
     * @return Person[]|Collection
     */
    public function getPersons(): \Doctrine\Common\Collections\Collection
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

    /**
     * Get father.
     */
    public function getFather(): ?\App\Entity\Person
    {
        return $this->father;
    }

    public function setFather(Person $father = null): self
    {
        $this->father = $father;

        if (!$this->father->hasGender()) {
            $this->father->setGender(Person::GENDER_MALE);
        }

        return $this;
    }

    public function getMother(): ?Person
    {
        return $this->mother;
    }

    /**
     * Set mother.
     */
    public function setMother(Person $mother = null): static
    {
        $this->mother = $mother;

        if (!$this->mother->hasGender()) {
            $this->mother->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    /**
     * Get language.
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Set language.
     *
     */
    public function setLanguage(?string $language = null): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get numberChildren.
     */
    public function getNumberChildren(): int
    {
        return $this->numberChildren;
    }

    /**
     * Set numberChildren.
     *
     *
     */
    public function setNumberChildren(int $numberChildren): static
    {
        $this->numberChildren = $numberChildren;

        return $this;
    }

    /**
     * Set address.
     *
     * @param string|null $address
     *
     * @return Family
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city.
     *
     * @param string|null $city
     *
     * @return Family
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get personAuthorized.
     */
    public function getPersonAuthorized(): ?string
    {
        return $this->personAuthorized;
    }

    /**
     * Set personAuthorized.
     *
     */
    public function setPersonAuthorized(?string $personAuthorized = null): static
    {
        $this->personAuthorized = $personAuthorized;

        return $this;
    }

    /**
     * Get personEmergency.
     */
    public function getPersonEmergency(): ?string
    {
        return $this->personEmergency;
    }

    /**
     * Set personEmergency.
     *
     */
    public function setPersonEmergency(?string $personEmergency = null): static
    {
        $this->personEmergency = $personEmergency;

        return $this;
    }

    /**
     * Add person.
     */
    public function addPerson(Person $person): static
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove person.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removePerson(Person $person): bool
    {
        return $this->persons->removeElement($person);
    }
}

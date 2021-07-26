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

    public function getNameComplete(): string
    {
        $persons = [];
        if ($this->mother !== null) {
            $persons[] = (string) $this->mother;
        }

        if ($this->father !== null) {
            $persons[] = (string) $this->father;
        }

        if (count($persons) < 2 && $this->legalGuardian !== null) {
            $persons[] = (string) $this->getLegalGuardian();
        }

        if (!empty($this->persons)) {
            if (count($persons) < 2 && !empty($this->persons?->get(0))) {
                $persons[] = sprintf('[%s]', (string) $this->persons?->first());
            }

            if (count($persons) < 2 && !empty($this->getPersons()->get(1))) {
                $persons[] = sprintf('[%s]', (string) $this->getPersons()->get(1));
            }

            if (count($this->getPersons()) > 2) {
                $persons[] = '...';
            }
        }

        return implode(', ', $persons);
    }

    public function __toString(): string
    {
        return sprintf('%d - %s', $this->getId(), $this->getNameComplete());
    }

    public function getId()
    {
        return $this->id;
    }


    public function setFather(Person $father = null): self
    {
        $this->father = $father;

        if (!$this->father->hasGender()) {
            $this->father->setGender(Person::GENDER_MALE);
        }

        return $this;
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
     *
     * @return Person|null
     */
    public function getFather()
    {
        return $this->father;
    }

    /**
     * Set mother.
     *
     * @return Family
     */
    public function setMother(Person $mother = null)
    {
        $this->mother = $mother;

        if (!$this->mother->hasGender()) {
            $this->mother->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    public function getMother(): ?Person
    {
        return $this->mother;
    }

    public function setLegalGuardian(Person $legalGuardian = null): self
    {
        $this->legalGuardian = $legalGuardian;

        if (!$this->legalGuardian->hasGender()) {
            $this->legalGuardian->setGender(Person::GENDER_FEMALE);
        }

        return $this;
    }

    public function getLegalGuardian(): ?Person
    {
        return $this->legalGuardian;
    }

    /**
     * Set language.
     *
     * @param string|null $language
     *
     * @return Family
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set numberChildren.
     *
     * @param int $numberChildren
     *
     * @return Family
     */
    public function setNumberChildren($numberChildren)
    {
        $this->numberChildren = $numberChildren;

        return $this;
    }

    /**
     * Get numberChildren.
     *
     * @return int
     */
    public function getNumberChildren()
    {
        return $this->numberChildren;
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
     * Set personAuthorized.
     *
     * @param string|null $personAuthorized
     *
     * @return Family
     */
    public function setPersonAuthorized($personAuthorized = null)
    {
        $this->personAuthorized = $personAuthorized;

        return $this;
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
     * Set personEmergency.
     *
     * @param string|null $personEmergency
     *
     * @return Family
     */
    public function setPersonEmergency($personEmergency = null)
    {
        $this->personEmergency = $personEmergency;

        return $this;
    }

    /**
     * Get personEmergency.
     *
     * @return string|null
     */
    public function getPersonEmergency()
    {
        return $this->personEmergency;
    }

    /**
     * Add person.
     *
     * @return Family
     */
    public function addPerson(Person $person)
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove person.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removePerson(Person $person)
    {
        return $this->persons->removeElement($person);
    }

    /**
     * Get persons.
     *
     * @return Person[]|Collection
     */
    public function getPersons()
    {
        return $this->persons;
    }
}

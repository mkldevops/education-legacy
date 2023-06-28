<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClassSchoolRepository;
use App\Traits\AuthorEntityTrait;
use App\Traits\SchoolEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClassSchoolRepository::class)
 */
class ClassSchool implements \Stringable
{
    use AuthorEntityTrait;
    use DescriptionEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SchoolEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @ORM\OneToMany(targetEntity=ClassPeriod::class, mappedBy="classSchool")
     */
    private Collection $classPeriods;

    /**
     * @Assert\Range(min="3", max="30")
     *
     * @ORM\Column(type="integer")
     */
    private int $ageMinimum = 3;

    /**
     * @Assert\Range(min="3", max="30")
     *
     * @ORM\Column(type="integer")
     */
    private int $ageMaximum = 3;

    public function __construct()
    {
        $this->setEnable(true)
            ->classPeriods = new ArrayCollection()
        ;
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function addClassPeriod(ClassPeriod $classPeriods): self
    {
        $this->classPeriods[] = $classPeriods;

        return $this;
    }

    public function removeClassPeriod(ClassPeriod $classPeriods): void
    {
        $this->classPeriods->removeElement($classPeriods);
    }

    public function getClassPeriods(): ArrayCollection
    {
        $classPeriods = new ArrayCollection();

        if (!empty($this->classPeriods)) {
            foreach ($this->classPeriods as $classPeriod) {
                // @var ClassPeriod $classPeriod

                $classPeriods[$classPeriod->getPeriod()->getId()] = $classPeriod;
            }
        }

        return $classPeriods;
    }

    public function getAgeMinimum(): int
    {
        return $this->ageMinimum;
    }

    public function setAgeMinimum(int $ageMinimum): self
    {
        $this->ageMinimum = $ageMinimum;

        return $this;
    }

    public function getAgeMaximum(): int
    {
        return $this->ageMaximum;
    }

    public function setAgeMaximum(int $ageMaximum): self
    {
        $this->ageMaximum = $ageMaximum;

        return $this;
    }

    public function current(Period $period): ?ClassPeriod
    {
        foreach ($this->classPeriods as $classPeriod) {
            if ($classPeriod->getPeriod()->getId() === $period->getId()) {
                return $classPeriod;
            }
        }

        return null;
    }
}

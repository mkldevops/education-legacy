<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeacherRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TeacherRepository::class)
 */
class Teacher implements \Stringable
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @ORM\ManyToMany(targetEntity=Course::class, inversedBy="teachers", cascade={"persist", "merge"})
     */
    protected Collection $courses;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, cascade={"persist", "merge", "remove"})
     */
    private ?Person $person = null;

    /**
     * @ORM\ManyToOne(targetEntity=Grade::class, cascade={"persist"})
     */
    private ?Grade $grade = null;

    /**
     * @ORM\ManyToMany(targetEntity=ClassPeriod::class, inversedBy="teachers", cascade={"persist", "merge"})
     */
    private Collection $classPeriods;

    public function __construct()
    {
        $this->classPeriods = new ArrayCollection();
        $this->courses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return empty($this->name) ? $this->person->getNameComplete() : $this->name;
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

    public function removeClassPeriod(ClassPeriod $classPeriods): self
    {
        $this->classPeriods->removeElement($classPeriods);

        return $this;
    }

    public function getClassPeriods(): Collection
    {
        return $this->classPeriods;
    }

    public function setClassPeriods(array|Collection $items): self
    {
        if ($items instanceof ArrayCollection || \is_array($items)) {
            foreach ($items as $item) {
                $this->addClassPeriod($item);
            }
        } elseif ($items instanceof ClassPeriod) {
            $this->addClassPeriod($items);
        }

        return $this;
    }

    public function addClassPeriod(ClassPeriod $classPeriods): self
    {
        $this->classPeriods->add($classPeriods);

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function addCourse(Course $courses): self
    {
        $this->courses[] = $courses;

        return $this;
    }

    public function removeCourse(Course $courses): void
    {
        $this->courses->removeElement($courses);
    }

    public function getCourses(): Collection
    {
        return $this->courses;
    }
}

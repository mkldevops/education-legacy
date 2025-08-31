<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\ClassPeriodRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClassPeriodRepository::class)]
#[UniqueEntity(fields: ['classSchool'], groups: ['classPeriod'])]
#[UniqueEntity(fields: ['period'], groups: ['classPeriod'])]
class ClassPeriod implements EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: ClassSchool::class, cascade: ['persist'], inversedBy: 'classPeriods')]
    #[ORM\JoinColumn(nullable: false)]
    private ClassSchool $classSchool;

    #[ORM\ManyToOne(targetEntity: Period::class, cascade: ['persist'], inversedBy: 'classPeriods')]
    #[ORM\JoinColumn(nullable: false)]
    private Period $period;

    /**
     * @var Collection<int, ClassPeriodStudent>
     */
    #[ORM\OneToMany(targetEntity: ClassPeriodStudent::class, mappedBy: 'classPeriod', cascade: ['persist'])]
    private Collection $students;

    /**
     * @var Collection<int, Teacher>
     */
    #[ORM\ManyToMany(targetEntity: Teacher::class, mappedBy: 'classPeriods', cascade: ['persist'])]
    private Collection $teachers;

    /**
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'classPeriod', cascade: ['persist'])]
    private Collection $courses;

    public function __construct()
    {
        $this->enable = true;
        $this->students = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->courses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->getClassSchool()->getName().' - '.$this->period->getName();
    }

    public function getClassSchool(): ClassSchool
    {
        return $this->classSchool;
    }

    public function setClassSchool(ClassSchool $classSchool): self
    {
        $this->classSchool = $classSchool;

        return $this;
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function setPeriod(Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function addStudent(ClassPeriodStudent $classPeriodStudent): self
    {
        $this->students[] = $classPeriodStudent;

        return $this;
    }

    public function removeStudent(ClassPeriodStudent $classPeriodStudent): void
    {
        $this->students->removeElement($classPeriodStudent);
    }

    /**
     * @return Collection<int, ClassPeriodStudent>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addCourse(Course $course): self
    {
        $this->courses[] = $course;

        return $this;
    }

    public function removeCourse(Course $course): void
    {
        $this->courses->removeElement($course);
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addTeacher(Teacher $teacher): self
    {
        $this->teachers[] = $teacher;

        return $this;
    }

    public function removeTeacher(Teacher $teacher): void
    {
        $this->teachers->removeElement($teacher);
    }

    /**
     * @return Collection<int, Teacher>
     */
    public function getTeachers(): Collection
    {
        return $this->teachers;
    }

    public function getTeachersToString(): string
    {
        $str = '';
        foreach ($this->teachers as $teacher) {
            $str .= ('' === $str || '0' === $str ? '' : ', ').$teacher;
        }

        return $str;
    }
}

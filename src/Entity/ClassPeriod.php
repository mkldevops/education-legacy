<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClassPeriodRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClassPeriodRepository::class)]
#[UniqueEntity(fields: ['classSchool'], groups: ['classPeriod'])]
#[UniqueEntity(fields: ['period'], groups: ['classPeriod'])]
class ClassPeriod implements \Stringable
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

    #[ORM\OneToMany(targetEntity: ClassPeriodStudent::class, mappedBy: 'classPeriod', cascade: ['persist'])]
    private Collection $students;

    #[ORM\ManyToMany(targetEntity: Teacher::class, mappedBy: 'classPeriods', cascade: ['persist'])]
    private Collection $teachers;

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

    public function addStudent(ClassPeriodStudent $students): self
    {
        $this->students[] = $students;

        return $this;
    }

    public function removeStudent(ClassPeriodStudent $students): void
    {
        $this->students->removeElement($students);
    }

    public function getStudents(): Collection
    {
        return $this->students;
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

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addTeacher(Teacher $teachers): self
    {
        $this->teachers[] = $teachers;

        return $this;
    }

    public function removeTeacher(Teacher $teachers): void
    {
        $this->teachers->removeElement($teachers);
    }

    public function getTeachers(): Collection
    {
        return $this->teachers;
    }

    public function getTeachersToString(): string
    {
        $str = '';
        foreach ($this->teachers as $teacher) {
            $str .= (empty($str) ? '' : ', ').$teacher;
        }

        return $str;
    }
}

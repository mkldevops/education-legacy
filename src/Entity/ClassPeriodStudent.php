<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClassPeriodStudentRepository;
use App\Traits\AuthorEntityTrait;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntity;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ClassPeriodStudentRepository::class)
 */
class ClassPeriodStudent
{
    use AuthorEntityTrait;
    use CommentEntity;
    use EnableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=ClassPeriod::class, inversedBy="students")
     */
    private ClassPeriod $classPeriod;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="classPeriods")
     */
    private Student $student;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $begin;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $end;

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->student, $this->classPeriod);
    }

    public function isActive(): bool
    {
        return $this->getEnd()->getTimestamp() < time() && $this->getEnable();
    }

    public function setEnd(DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set begin.
     *
     * @return static
     * @param \DateTime|\DateTimeImmutable $begin
     */
    public function setBegin(\DateTimeInterface $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get begin.
     *
     * @return \Datetime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set classPeriod.
     *
     * @return static
     */
    public function setClassPeriod(ClassPeriod $classPeriod)
    {
        $this->classPeriod = $classPeriod;

        return $this;
    }

    /**
     * Get classPeriod.
     *
     * @return ClassPeriod
     */
    public function getClassPeriod()
    {
        return $this->classPeriod;
    }

    /**
     * Set student.
     *
     * @return static
     */
    public function setStudent(Student $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student.
     *
     * @return Student
     */
    public function getStudent()
    {
        return $this->student;
    }
}

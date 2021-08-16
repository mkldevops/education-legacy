<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClassPeriodStudentRepository;
use App\Traits\AuthorEntityTrait;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ClassPeriodStudentRepository::class)
 */
class ClassPeriodStudent
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=ClassPeriod::class, inversedBy="students")
     */
    private ?ClassPeriod $classPeriod = null;

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

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get begin.
     */
    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * Set begin.
     *
     * @param DateTime|DateTimeImmutable $begin
     *
     */
    public function setBegin(DateTimeInterface $begin): static
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get classPeriod.
     */
    public function getClassPeriod(): ?\App\Entity\ClassPeriod
    {
        return $this->classPeriod;
    }

    /**
     * Set classPeriod.
     */
    public function setClassPeriod(ClassPeriod $classPeriod): static
    {
        $this->classPeriod = $classPeriod;

        return $this;
    }

    /**
     * Get student.
     */
    public function getStudent(): \App\Entity\Student
    {
        return $this->student;
    }

    /**
     * Set student.
     */
    public function setStudent(Student $student): static
    {
        $this->student = $student;

        return $this;
    }
}

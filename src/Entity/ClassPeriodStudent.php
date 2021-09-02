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
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ClassPeriodStudentRepository::class)
 */
class ClassPeriodStudent
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntityTrait;

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
    private ?DateTimeInterface $end = null;

    public function __toString(): string
    {
        return sprintf('%s - %s', (string) $this->student, (string) $this->classPeriod);
    }

    public function isActive(): bool
    {
        return $this->getEnd()?->getTimestamp() < time() && $this->getEnable();
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }


    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(DateTimeInterface $begin): static
    {
        $this->begin = $begin;

        return $this;
    }

    public function getClassPeriod(): ?ClassPeriod
    {
        return $this->classPeriod;
    }

    public function setClassPeriod(ClassPeriod $classPeriod): static
    {
        $this->classPeriod = $classPeriod;

        return $this;
    }

    public function getStudent(): Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): static
    {
        $this->student = $student;

        return $this;
    }
}

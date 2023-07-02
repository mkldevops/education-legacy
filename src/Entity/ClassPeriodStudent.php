<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClassPeriodStudentRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassPeriodStudentRepository::class)]
class ClassPeriodStudent implements \Stringable
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: ClassPeriod::class, inversedBy: 'students')]
    private ?ClassPeriod $classPeriod = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'classPeriods')]
    private Student $student;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $begin;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $end = null;

    public function __toString(): string
    {
        return sprintf('%s - %s', (string) $this->student, (string) $this->classPeriod);
    }

    public function isActive(): bool
    {
        if ($this->getEnd()?->getTimestamp() >= time()) {
            return false;
        }

        return $this->getEnable();
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTimeInterface $begin): static
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

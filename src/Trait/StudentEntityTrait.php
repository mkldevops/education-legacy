<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\Student;
use Doctrine\ORM\Mapping as ORM;

trait StudentEntityTrait
{
    #[ORM\ManyToOne(targetEntity: Student::class, cascade: ['persist', 'remove'])]
    protected ?Student $student = null;

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): self
    {
        $this->student = $student;

        return $this;
    }
}

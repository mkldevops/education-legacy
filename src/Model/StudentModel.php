<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Student;

class StudentModel
{
    public function __construct(
        public readonly int $id,
        public readonly PersonModel $person,
    ) {
    }

    public static function fromStudent(Student $student): self
    {
        return new self(
            id: $student->getId(),
            person: PersonModel::fromPerson($student->getPerson())
        );
    }
}

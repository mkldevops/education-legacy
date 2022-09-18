<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Person;

class PersonModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $forname,
        public readonly ?\DateTimeInterface $birthday,
        public readonly string $gender,
    ) {
    }

    public static function fromPerson(Person $person): self
    {
        return new self(
            id: $person->getId(),
            name: $person->getName(),
            forname: $person->getForname(),
            birthday: $person->getBirthday(),
            gender: $person->getGender()
        );
    }
}

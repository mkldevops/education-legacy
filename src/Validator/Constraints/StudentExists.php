<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StudentExists extends Constraint
{
    public string $message = 'The student with ID {{ id }} does not exist.';
}

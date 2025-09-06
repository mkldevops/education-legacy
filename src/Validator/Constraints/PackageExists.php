<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PackageExists extends Constraint
{
    public string $message = 'The package with ID {{ id }} does not exist.';
}

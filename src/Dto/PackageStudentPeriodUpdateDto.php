<?php

declare(strict_types=1);

namespace App\Dto;

use App\Validator\Constraints\PackageExists;
use Symfony\Component\Validator\Constraints as Assert;

class PackageStudentPeriodUpdateDto
{
    #[Assert\NotBlank(message: 'Package ID is required')]
    #[Assert\Type(type: 'integer', message: 'Package ID must be an integer')]
    #[Assert\Positive(message: 'Package ID must be positive')]
    #[PackageExists]
    public int $packageId;

    #[Assert\Type(type: 'numeric', message: 'Discount must be a number')]
    #[Assert\PositiveOrZero(message: 'Discount must be positive or zero')]
    public float $discount = 0.0;

    #[Assert\Length(max: 1000, maxMessage: 'Comment cannot be longer than {{ limit }} characters')]
    public ?string $comment = null;
}

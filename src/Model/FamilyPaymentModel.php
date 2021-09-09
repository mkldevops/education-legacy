<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Family;

class FamilyPaymentModel
{
    public function __construct(
        public ?Family $family = null,
        public array $persons = [],
        public array $packages = [],
        public float $amountTotal = 0.00,
        public float $toPay = 0.00
    ) {
    }
}

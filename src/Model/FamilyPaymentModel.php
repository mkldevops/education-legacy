<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Family;
use App\Entity\Operation;
use App\Exception\InvalidArgumentException;

class FamilyPaymentModel
{
    public float $toPay = 0.00;
    public float $toDue = 0.00;
    public array $payments = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public Operation $operation,
        public Family $family,
        public array $packages = [],
    ) {
        $this->toPay = $operation->getAmount();
        $this->toDue = self::calculateToDue($packages, $this->toPay);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function calculateToDue(array $packages, float $toPay): float
    {
        $toDue = 0.00;
        foreach ($packages as $package) {
            $toDue += $package->getUnpaid();
        }

        if ($toPay > $toDue) {
            throw new InvalidArgumentException(sprintf('Amount that you want to pay (%d €) is too high that you due amount (%d €)', $toPay, $toDue));
        }

        return $toDue;
    }
}

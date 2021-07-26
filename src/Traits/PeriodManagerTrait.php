<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\Period;
use App\Exception\PeriodException;
use App\Manager\PeriodManager;

trait PeriodManagerTrait
{
    protected PeriodManager $periodManager;

    /**
     * @required
     */
    public function setPeriodManager(PeriodManager $periodManager): void
    {
        $this->periodManager = $periodManager;
    }

    /**
     * @throws PeriodException
     */
    public function findPeriod(int $id): Period
    {
        return $this->periodManager->findPeriod($id);
    }
}

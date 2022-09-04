<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Period;
use App\Model\PeriodsList;

interface PeriodManagerInterface
{
    public function findCurrentPeriod(): Period;

    public function findPeriod(?int $id): Period;

    public function getPeriodsOnSession(string $type = PeriodsList::PERIOD_SELECTED): Period;

    public function setPeriodsOnSession(): void;

    public function getEntityPeriodOnSession(string $type = PeriodsList::PERIOD_SELECTED): Period;
}

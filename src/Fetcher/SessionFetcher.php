<?php

declare(strict_types=1);

namespace App\Fetcher;

use App\Entity\Period;
use App\Entity\School;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Exception\SchoolException;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;

class SessionFetcher implements SessionFetcherInterface
{
    public function __construct(
        private readonly PeriodManager $periodManager,
        private readonly SchoolManager $schoolManager,
    ) {}

    /**
     * @throws InvalidArgumentException
     * @throws PeriodException
     */
    public function getPeriodOnSession(): Period
    {
        return $this->periodManager->getPeriodsOnSession();
    }

    /**
     * @throws SchoolException
     */
    public function getSchoolOnSession(): School
    {
        return $this->schoolManager->getSchoolOnSession();
    }

    /**
     * @throws InvalidArgumentException
     * @throws PeriodException
     */
    public function getEntityPeriodOnSession(): Period
    {
        return $this->periodManager->getEntityPeriodOnSession();
    }

    /**
     * @throws SchoolException
     */
    public function getEntitySchoolOnSession(): School
    {
        return $this->schoolManager->getEntitySchoolOnSession();
    }
}

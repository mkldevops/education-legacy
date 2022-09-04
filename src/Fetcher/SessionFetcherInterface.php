<?php

declare(strict_types=1);

namespace App\Fetcher;

use App\Entity\Period;
use App\Entity\School;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;

interface SessionFetcherInterface
{
    /**
     * @throws InvalidArgumentException
     * @throws PeriodException
     */
    public function getPeriodOnSession(): Period;

    public function getSchoolOnSession(): School;

    public function getEntityPeriodOnSession(): Period;

    public function getEntitySchoolOnSession(): School;
}

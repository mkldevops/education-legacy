<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\Period;
use App\Exception\PeriodException;
use App\Manager\PeriodManager;
use Symfony\Contracts\Service\Attribute\Required;

trait PeriodManagerTrait
{
    protected PeriodManager $periodManager;

    #[Required]
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

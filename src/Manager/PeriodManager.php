<?php

declare(strict_types=1);


namespace App\Manager;

use App\Entity\Period;
use App\Exception\AppException;
use App\Exception\PeriodException;
use App\Model\PeriodsList;
use App\Repository\PeriodRepository;
use App\Services\AbstractFullService;
use Fardus\Traits\Symfony\Manager\SessionTrait;

class PeriodManager
{
    use SessionTrait;

    public function __construct(private PeriodRepository $repository)
    {
    }



    public function findCurrentPeriod(): Period
    {
        return $this->repository->getCurrentPeriod();
    }

    /**
     * @throws PeriodException
     */
    public function findPeriod(?int $id): Period
    {
        if ($id === null) {
            throw new PeriodException('Period id cannot be null');
        }

        $period = $this->repository->find($id);
        if (!$period instanceof Period) {
            throw new PeriodException(sprintf('Not found Period %d', $id));
        }

        return $period;
    }

    /**
     * @throws AppException
     */
    public function setPeriodsOnSession(): void
    {
        $list = $this->repository->findBy(['enable' => true]);

        if (empty($list)) {
            throw new AppException('Nothing period available');
        }

        $current = $this->repository->getCurrentPeriod();
        $this->session->set('period', new PeriodsList($list, $current, $current));
    }

    /**
     * @throws AppException
     */
    public function switch(Period $period): void
    {
        $periodList = $this->session->get('period');
        if (!$periodList instanceof PeriodsList) {
            throw new AppException();
        }

        $periodList->selected = $period;
        $this->session->set('period', $periodList);
        $this->session->save();
    }
}

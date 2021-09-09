<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Period;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Model\PeriodsList;
use App\Repository\PeriodRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PeriodManager
{
    public function __construct(private PeriodRepository $repository, private RequestStack $requestStack)
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
        if (null === $id) {
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
     * @throws InvalidArgumentException
     */
    public function getPeriodsOnSession(string $type = PeriodsList::PERIOD_SELECTED): Period
    {
        if (!property_exists(PeriodsList::class, $type)) {
            throw new InvalidArgumentException('Not found the type to Period search');
        }

        if (empty($this->requestStack->getSession()->get('period'))) {
            $this->setPeriodsOnSession();
        }

        return $this->requestStack->getSession()->get('period')->{$type};
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
        $this->requestStack->getSession()->set('period', new PeriodsList($list, $current, $current));
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    public function getEntityPeriodOnSession(string $type = 'selected'): Period
    {
        if (!in_array($type, ['selected', 'current', 'list'], true)) {
            throw new InvalidArgumentException('Not found the type to Period search');
        }

        $typePeriod = $this->getPeriodsOnSession($type);

        return $this->findPeriod($typePeriod->getId());
    }

    /**
     * @throws AppException
     */
    public function switch(Period $period): void
    {
        $periodList = $this->requestStack->getSession()->get('period');
        if (!$periodList instanceof PeriodsList) {
            throw new AppException();
        }

        $periodList->selected = $period;
        $this->requestStack->getSession()->set('period', $periodList);
        $this->requestStack->getSession()->save();
    }
}

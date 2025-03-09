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

class PeriodManager implements PeriodManagerInterface
{
    public function __construct(
        private readonly PeriodRepository $periodRepository,
        private readonly RequestStack $requestStack
    ) {}

    public function findCurrentPeriod(): Period
    {
        return $this->periodRepository->getCurrentPeriod();
    }

    /**
     * @throws PeriodException
     */
    public function findPeriod(?int $id): Period
    {
        if (null === $id) {
            throw new PeriodException('Period id cannot be null');
        }

        $period = $this->periodRepository->find($id);
        if (!$period instanceof Period) {
            throw new PeriodException(\sprintf('Not found Period %d', $id));
        }

        return $period;
    }

    /**
     * @throws InvalidArgumentException
     * @throws PeriodException
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
     * @throws PeriodException
     */
    public function setPeriodsOnSession(): void
    {
        $list = $this->periodRepository->findBy(['enable' => true]);

        if ([] === $list) {
            throw new PeriodException('Nothing period available');
        }

        $period = $this->periodRepository->getCurrentPeriod();
        $this->requestStack->getSession()->set('period', new PeriodsList($list, $period, $period));
    }

    /** AppException.
     * @throws InvalidArgumentException
     * @throws PeriodException
     */
    public function getEntityPeriodOnSession(string $type = 'selected'): Period
    {
        if (!\in_array($type, ['selected', 'current', 'list'], true)) {
            throw new InvalidArgumentException('Not found the type to Period search');
        }

        $typePeriod = $this->getPeriodsOnSession($type);

        return $this->findPeriod($typePeriod->getId());
    }

    /**
     * @throws PeriodException
     */
    public function switch(Period $period): void
    {
        $periodList = $this->requestStack->getSession()->get('period');
        if (!$periodList instanceof PeriodsList) {
            throw new PeriodException();
        }

        $periodList->selected = $period;
        $this->requestStack->getSession()->set('period', $periodList);
        $this->requestStack->getSession()->save();
    }
}

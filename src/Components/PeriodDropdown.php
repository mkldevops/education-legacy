<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Period;
use App\Model\PeriodsList;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final readonly class PeriodDropdown
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public function getPeriods(): array
    {
        $periodsList = $this->getPeriodsListObject();

        if (!$periodsList instanceof PeriodsList) {
            return [];
        }

        return $periodsList->list ?? [];
    }

    public function getSelectedPeriod(): ?Period
    {
        $periodsList = $this->getPeriodsListObject();

        return $periodsList?->selected;
    }

    public function isPeriodActive(Period $period): bool
    {
        $selectedPeriod = $this->getSelectedPeriod();

        if (!$selectedPeriod instanceof Period) {
            return false;
        }

        return $period->getId() === $selectedPeriod->getId();
    }

    public function hasPeriods(): bool
    {
        return [] !== $this->getPeriods();
    }

    private function getPeriodsListObject(): ?PeriodsList
    {
        $session = $this->requestStack->getSession();

        return $session->get('period');
    }
}

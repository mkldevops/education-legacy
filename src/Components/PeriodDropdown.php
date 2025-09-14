<?php

declare(strict_types=1);

namespace App\Components;

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
        $session = $this->requestStack->getSession();

        return $session->get('period')['list'] ?? [];
    }

    public function getSelectedPeriod(): ?object
    {
        $session = $this->requestStack->getSession();

        return $session->get('period')['selected'] ?? null;
    }

    public function isPeriodActive(object $period): bool
    {
        $selectedPeriod = $this->getSelectedPeriod();

        if (!$selectedPeriod || !isset($period->id, $selectedPeriod->id)) {
            return false;
        }

        return $period->id === $selectedPeriod->id;
    }

    public function hasPeriods(): bool
    {
        return [] !== $this->getPeriods();
    }
}

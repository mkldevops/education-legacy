<?php

declare(strict_types=1);

namespace App\Components;

use App\Entity\Student;
use App\Repository\ClassPeriodRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ClassPeriodModal
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public bool $isOpen = false;

    #[LiveProp(writable: true)]
    public ?int $studentId = null;

    #[LiveProp(writable: true)]
    public ?int $selectedClassPeriodId = null;

    public function __construct(
        private readonly ClassPeriodRepository $classPeriodRepository,
        private readonly RequestStack $requestStack
    ) {}

    #[LiveAction]
    public function open(): void
    {
        $this->isOpen = true;
    }

    #[LiveAction]
    public function openForStudent(int $studentId): void
    {
        $this->studentId = $studentId;
        $this->isOpen = true;
    }

    #[LiveAction]
    public function close(): void
    {
        $this->isOpen = false;
        $this->studentId = null;
        $this->selectedClassPeriodId = null;
    }

    #[LiveAction]
    public function changeClassPeriod(): void
    {
        // This would typically handle the class period change logic
        // For now, we just close the modal
        $this->close();

        // Here you would add the logic to actually change the student's class period
        // Example: $this->studentManager->changeClassPeriod($this->studentId, $this->selectedClassPeriodId);
    }

    public function getClassPeriods(): array
    {
        $session = $this->requestStack->getSession();
        $periodData = $session->get('period');

        if (!$periodData || !isset($periodData->selected)) {
            return [];
        }

        return $this->classPeriodRepository->findBy([
            'period' => $periodData->selected,
        ]);
    }

    public function hasClassPeriods(): bool
    {
        return [] !== $this->getClassPeriods();
    }
}

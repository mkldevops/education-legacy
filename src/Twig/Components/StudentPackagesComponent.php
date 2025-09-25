<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\PackageStudentPeriod;
use App\Entity\Student;
use App\Repository\PackageStudentPeriodRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('student_packages')]
final class StudentPackagesComponent
{
    use DefaultActionTrait;

    private const STATUS_META = [
        PackageStudentPeriod::STATUS_PAYMENT_SUCCESS => [
            'label' => 'Réglé',
            'badge' => 'bg-green-100 text-green-800 border-green-200',
            'progress' => 'bg-green-500',
            'icon' => 'tabler:circle-check',
        ],
        PackageStudentPeriod::STATUS_PAYMENT_WARNING => [
            'label' => 'À surveiller',
            'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
            'progress' => 'bg-amber-500',
            'icon' => 'tabler:alert-triangle',
        ],
        PackageStudentPeriod::STATUS_PAYMENT_DANGER => [
            'label' => 'En retard',
            'badge' => 'bg-red-100 text-red-800 border-red-200',
            'progress' => 'bg-red-500',
            'icon' => 'tabler:alert-circle',
        ],
        PackageStudentPeriod::STATUS_PAYMENT_INFO => [
            'label' => 'En cours',
            'badge' => 'bg-sky-100 text-sky-800 border-sky-200',
            'progress' => 'bg-sky-500',
            'icon' => 'tabler:info-circle',
        ],
    ];

    #[LiveProp]
    public ?Student $student = null;

    private ?array $packagePeriodsCache = null;

    public function __construct(
        private readonly PackageStudentPeriodRepository $packageStudentPeriodRepository,
    ) {}

    /**
     * @return PackageStudentPeriod[]
     */
    public function getPackagePeriods(): array
    {
        if (!$this->student instanceof Student) {
            return [];
        }

        if (null !== $this->packagePeriodsCache) {
            return $this->packagePeriodsCache;
        }

        $this->packagePeriodsCache = $this->packageStudentPeriodRepository->getListToStudent($this->student);

        return $this->packagePeriodsCache;
    }

    /**
     * @return array{count:int, amount:float, discount:float, paid:float, unpaid:float}
     */
    public function getSummary(): array
    {
        $totalAmount = 0.0;
        $totalDiscount = 0.0;
        $totalUnpaid = 0.0;

        foreach ($this->getPackagePeriods() as $packageStudentPeriod) {
            $totalAmount += $packageStudentPeriod->getAmount();
            $totalDiscount += $packageStudentPeriod->getDiscount();
            $totalUnpaid += max(0, $packageStudentPeriod->getUnpaid());
        }

        $totalPaid = max(0, $totalAmount - $totalDiscount - $totalUnpaid);

        return [
            'count' => \count($this->getPackagePeriods()),
            'amount' => $totalAmount,
            'discount' => $totalDiscount,
            'paid' => $totalPaid,
            'unpaid' => $totalUnpaid,
        ];
    }

    /**
     * @return array{label:string,badge:string,progress:string,icon:string}
     */
    public function statusMeta(PackageStudentPeriod $packageStudentPeriod): array
    {
        $status = $packageStudentPeriod->getStatusPayments();

        return self::STATUS_META[$status] ?? self::STATUS_META[PackageStudentPeriod::STATUS_PAYMENT_INFO];
    }
}

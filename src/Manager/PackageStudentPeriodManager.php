<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\PackageStudentPeriod;
use App\Fetcher\SessionFetcherInterface;
use App\Repository\PackageStudentPeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

class PackageStudentPeriodManager
{
    public function __construct(
        private readonly PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionFetcherInterface $sessionFetcher
    ) {}

    public function add(PackageStudentPeriod $packageStudentPeriod): PackageStudentPeriod
    {
        $period = $this->sessionFetcher->getEntityPeriodOnSession();
        $result = $this->packageStudentPeriodRepository->findOneBy([
            'student' => $packageStudentPeriod->getStudent(),
            'period' => $period,
            'package' => $packageStudentPeriod->getPackage(),
        ]);

        if ($result instanceof PackageStudentPeriod) {
            return $result;
        }

        $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()?->getPrice())
            ->setPeriod($period)
            ->setDateExpire($period->getEnd())
        ;

        $this->persistData($packageStudentPeriod);

        return $packageStudentPeriod;
    }

    public function edit(PackageStudentPeriod $packageStudentPeriod): PackageStudentPeriod
    {
        $this->persistData($packageStudentPeriod);

        return $packageStudentPeriod;
    }

    private function persistData(PackageStudentPeriod $packageStudentPeriod): void
    {
        $this->entityManager->persist($packageStudentPeriod);
        $this->entityManager->flush();
    }
}

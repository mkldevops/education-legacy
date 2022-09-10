<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\PackageStudentPeriod;
use App\Fetcher\SessionFetcherInterface;
use App\Repository\PackageStudentPeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class PackageStudentPeriodManager
{
    public function __construct(
        private readonly PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionFetcherInterface $sessionFetcher,
        private readonly Security $security,
        private readonly LoggerInterface $logger
    ) {
    }

    public function add(PackageStudentPeriod $packageStudentPeriod): PackageStudentPeriod
    {
        $period = $this->sessionFetcher->getEntityPeriodOnSession();
        $result = $this->packageStudentPeriodRepository->findOneBy([
            'student' => $packageStudentPeriod->getStudent(),
            'period' => $period,
            'package' => $packageStudentPeriod->getPackage(),
        ]);

        if (null !== $result) {
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
        $this->logger->debug(__METHOD__, ['packageStudentPeriod' => $packageStudentPeriod]);

        $packageStudentPeriod->setAuthor($this->security->getUser());

        $this->entityManager->persist($packageStudentPeriod);
        $this->entityManager->flush();
    }
}

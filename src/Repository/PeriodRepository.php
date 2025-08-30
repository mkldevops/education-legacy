<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Period>
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Period::class);
    }

    /**
     * @return Period[]
     */
    public function getLastPeriods(Period $period): array
    {
        return $this->createQueryBuilder('per', 'per.id')
            ->where('per.begin <= :begin')
            ->andWhere('per.enable = 1')
            ->setParameter('begin', $period->getBegin())
            ->getQuery()->getResult()
        ;
    }

    public function getCurrentPeriod(): Period
    {
        /** @var Period[] $periods */
        $periods = $this->createQueryBuilder('per')
            ->where('CURRENT_TIMESTAMP() BETWEEN per.begin AND per.end')
            ->getQuery()
            ->getResult()
        ;

        return current($periods);
    }

    /**
     * @return Period[]
     */
    public function getAvailable(): array
    {
        return $this->createQueryBuilder('per')
            ->where('per.enable = 1')
            ->andWhere('per.end > CURRENT_TIMESTAMP()')
            ->getQuery()
            ->getResult()
        ;
    }
}

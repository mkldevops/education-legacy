<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Period|null find($id, $lockMode = null, $lockVersion = null)
 * @method Period|null findOneBy(array $criteria, array $orderBy = null)
 * @method Period[]    findAll()
 * @method Period[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }

    /**
     * Get list Students.
     *
     * @return Period[]
     */
    public function getLastPeriods(Period $period): array
    {
        return $this->createQueryBuilder('per', 'per.id')
            ->where('per.begin <= :begin')
            ->andWhere('per.enable = 1')
            ->setParameter('begin', $period->getBegin())
            ->getQuery()->getResult();
    }

    /**
     * Get current period.
     *
     * @return Period
     */
    public function getCurrentPeriod()
    {
        $aPeriod = $this->createQueryBuilder('per')
            ->where('CURRENT_TIMESTAMP() BETWEEN per.begin AND per.end')
            ->getQuery()
            ->getResult();

        return current($aPeriod);
    }

    /**
     * Get period available.
     *
     * @return QueryBuilder
     */
    public function getAvailable()
    {
        return $this->createQueryBuilder('per')
            ->where('per.enable = 1')
            ->andWhere('per.end > CURRENT_TIMESTAMP()');
    }
}

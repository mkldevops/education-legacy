<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PackageStudentPeriod>
 */
class PackageStudentPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PackageStudentPeriod::class);
    }

    /**
     * Get Current Package to Student.
     *
     * @throws NonUniqueResultException
     */
    public function getCurrentPackageStudent(int $sIdStudent, Period $period): ?PackageStudentPeriod
    {
        $queryBuilder = $this->createQueryBuilder('psd');

        $queryBuilder->innerJoin('psd.package', 'pck')
            ->innerJoin('psd.student', 'std')
            ->innerJoin('psd.period', 'per')
            ->innerJoin('psd.author', 'usr')
            ->addSelect('usr')
            ->addSelect('per')
            ->addSelect('std')
            ->addSelect('pck')
            ->where($queryBuilder->expr()->eq('std.id', $sIdStudent))
            ->andWhere('per.id = :period')
            ->setParameter('period', $period)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Get List ToStudent.
     *
     * @return PackageStudentPeriod[]
     */
    public function getListToStudent(Student $student): array
    {
        return $this->_em
            ->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id')
            ->leftJoin('p.payments', 'pay')
            ->leftJoin('pay.operation', 'o')
            ->where('p.student = :student')
            ->setParameter('student', $student)
            ->orderBy('p.period', 'DESC')
            ->addOrderBy('o.date', 'ASC')
            ->addOrderBy('o.datePlanned', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}

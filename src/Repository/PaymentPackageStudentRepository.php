<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Family;
use App\Entity\PaymentPackageStudent;
use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentPackageStudent>
 */
class PaymentPackageStudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PaymentPackageStudent::class);
    }

    /**
     * @return PaymentPackageStudent[]
     */
    public function findForFamilyAndPeriod(Family $family, Period $period): array
    {
        return $this->createQueryBuilder('payment')
            ->addSelect('packagePeriod', 'student', 'person', 'operation')
            ->innerJoin('payment.packageStudentPeriod', 'packagePeriod')
            ->innerJoin('packagePeriod.student', 'student')
            ->innerJoin('student.person', 'person')
            ->innerJoin('person.family', 'family')
            ->innerJoin('payment.operation', 'operation')
            ->andWhere('family = :family')
            ->andWhere('packagePeriod.period = :period')
            ->setParameter('family', $family)
            ->setParameter('period', $period)
            ->orderBy('operation.date', 'DESC')
            ->addOrderBy('person.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}

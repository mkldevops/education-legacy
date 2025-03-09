<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Entity\Period;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassPeriodStudent>
 */
class ClassPeriodStudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, ClassPeriodStudent::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getCurrentClassPeriodStudent(Student $student, Period $period): ?ClassPeriodStudent
    {
        return $this->createQueryBuilder('cps')
            ->join('cps.classPeriod', 'cp')
            ->where('cps.student = :student')
            ->andWhere('cp.period = :period')
            ->setParameter('student', $student)
            ->setParameter('period', $period)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function remove(ClassPeriod $classPeriod): mixed
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.classPeriod = :classPeriod')
            ->setParameter('classPeriod', $classPeriod)
            ->getQuery()
            ->execute()
        ;
    }
}

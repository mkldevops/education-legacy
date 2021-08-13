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
 * ClassPeriodStudentRepository.
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 *
 * @method ClassPeriodStudent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassPeriodStudent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassPeriodStudent[]    findAll()
 * @method ClassPeriodStudent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassPeriodStudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassPeriodStudent::class);
    }

    /**
     * Get Current ClassPeriodStudent.
     *
     * @return ClassPeriodStudent
     *
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
            ->getOneOrNullResult();
    }

    public function remove(ClassPeriod $classPeriod)
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.classPeriod = :classPeriod')
            ->setParameter('classPeriod', $classPeriod)
            ->getQuery()
            ->execute();
    }
}

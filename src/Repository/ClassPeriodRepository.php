<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\Student;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ClassSchoolRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * @method null|ClassPeriod find($id, $lockMode = null, $lockVersion = null)
 * @method null|ClassPeriod findOneBy(array $criteria, array $orderBy = null)
 * @method ClassPeriod[]    findAll()
 * @method ClassPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private CourseRepository $courseRepository, private ClassPeriodStudentRepository $classPeriodStudentRepository)
    {
        parent::__construct($registry, ClassPeriod::class);
    }

    public function remove(ClassPeriod $classPeriod): array
    {
        if (!$classPeriod->getCourses()?->isEmpty()) {
            $this->courseRepository->remove($classPeriod);
        }

        if (!$classPeriod->getStudents()->isEmpty()) {
            $this->classPeriodStudentRepository->remove($classPeriod);
        }

        return $this->createQueryBuilder('cp')
            ->delete()
            ->join('cp.courses', 'c')
            ->where('cp.id = :classPeriod')
            ->setParameter('classPeriod', $classPeriod)
            ->getQuery()
            ->execute()
        ;
    }

    public function getClassPeriods(Period $period, School $school): array
    {
        return $this->getClassPeriodsQueryBuilder($period, $school)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getClassPeriodsQueryBuilder(Period $period, School $school): QueryBuilder
    {
        return $this->createQueryBuilder('clp')
            ->leftJoin('clp.classSchool', 'cls')
            ->where('clp.period = :period')
            ->andWhere('cls.school = :school')
            ->setParameter('period', $period->getId())
            ->setParameter('school', $school->getId())
        ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getClassPeriodByName(string $name, Period $period, School $school): ?ClassPeriod
    {
        return $this->getClassPeriodsQueryBuilder($period, $school)
            ->andWhere('cls.name = :name')
            ->setParameter('name', trim($name))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return ClassPeriodStudent[]
     */
    public function getStudentToClassPeriod(ClassPeriod $classPeriod, Student $student = null): array
    {
        $qb = $this->_em
            ->createQueryBuilder()
            ->select('cps')
            ->from(ClassPeriodStudent::class, 'cps')
            ->join('cps.student', 's')
            ->join('s.person', 'p')
            ->where('cps.classPeriod = :classPeriod')
            ->andWhere('s.enable = true')
            ->andWhere('cps.end >= :now')
            ->setParameter('classPeriod', $classPeriod->getId())
            ->setParameter('now', new DateTime())
            ->orderBy('p.name', 'ASC')
        ;

        if (null !== $student) {
            $qb->andWhere('cps.student = :student')
                ->setParameter('student', $student->getId())
            ;
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ClassPeriod[]
     */
    public function getListOfCurrentPeriod(Period $period, School $school): array
    {
        return $this->getQueryBuilderList($school)
            ->andWhere('cp.period = :period')
            ->setParameter(':period', $period)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countClassesPeriod(School $school, string $search): int
    {
        return (int) $this->getQueryBuilderList($school, $search)
            ->select('count(cp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getList(School $school, int $page, string $search): array
    {
        return $this->getQueryBuilderList($school, $search)
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getQueryBuilderList(School $school, string $search = ''): QueryBuilder
    {
        return $this->createQueryBuilder('cp')
            ->innerJoin('cp.classSchool', 'cs', Join::WITH, 'cs.school = :school')
            ->setParameter(':school', $school)
            ->where('cp.comment LIKE :comment')
            ->setParameter(':comment', '%'.$search.'%')
            ->orWhere('cp.enable LIKE :status')
            ->setParameter(':status', '%'.$search.'%')
        ;
    }
}

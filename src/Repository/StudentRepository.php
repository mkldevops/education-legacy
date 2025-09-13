<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Family;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\Student;
use App\Exception\AppException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Fardus\Traits\Symfony\Manager\LoggerTrait;

/**
 * @extends ServiceEntityRepository<Student>
 */
class StudentRepository extends ServiceEntityRepository
{
    use LoggerTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Student::class);
    }

    /**
     * @return Student[]
     */
    public function findByFamily(Family $family): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.person', 'p')
            ->where('p.family = :family')
            ->andWhere('s.enable = true')
            ->setParameter('family', $family)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getStatsNumberStudent(School $school): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s) AS nb')
            ->where('s.school = :school')
            ->setParameter('school', $school)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Paginator<Student>
     */
    public function getStudents(int $iPage = 1, int $iCountPerPage = 20): Paginator
    {
        $oQuery = $this->createQueryBuilder('s')
            ->orderBy('s.name', 'DESC')
            ->leftJoin('s.grade', 'g')
            ->addSelect('g')
            ->getQuery()
        ;

        $oQuery->setFirstResult(($iPage - 1) * $iCountPerPage)
            ->setMaxResults($iCountPerPage)
        ;

        return new Paginator($oQuery);
    }

    /**
     * @return Student[]
     */
    public function getListStudents(Period $period, School $school, bool $enable = true, ?int $limit = null): array
    {
        // MAIN QUERY: Load students with ManyToOne/OneToOne relations only (no cartesian products)
        $queryBuilder = $this->createQueryBuilder('std')
            ->innerJoin('std.grade', 'gra')

            // Critical ManyToOne relations (safe from cartesian products)
            ->leftJoin('std.person', 'prs')              // Person (name, phones, etc.)
            ->leftJoin('prs.family', 'fam')              // Family (family ID, contact info)
            ->leftJoin('prs.image', 'img')               // Student photo/image

            // SELECT core relations
            ->addSelect('gra')    // Grade
            ->addSelect('prs')    // Person (eliminates student.person queries)
            ->addSelect('fam')    // Family (eliminates student.person.family queries)
            ->addSelect('img')    // Image (eliminates student.image queries)

            ->where('std.enable = :enable')
            ->andWhere('std.school = :school')
            ->setParameter('period', $period)
            ->setParameter('enable', $enable)
            ->setParameter('school', $school)
        ;

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $students = $queryBuilder->getQuery()->getResult();

        // BATCH LOAD: Fetch OneToMany relations separately to avoid cartesian products
        if (!empty($students)) {
            $studentIds = array_map(static fn ($student) => $student->getId(), $students);

            // Load ClassPeriodStudent relations in separate query
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('cps, clp, cls')
                ->from('App\Entity\ClassPeriodStudent', 'cps')
                ->leftJoin('cps.classPeriod', 'clp', 'WITH', 'clp.period = :period')
                ->leftJoin('clp.classSchool', 'cls')
                ->where('cps.student IN (:students)')
                ->setParameter('period', $period)
                ->setParameter('students', $studentIds)
                ->getQuery()
                ->getResult()
            ;
        }

        return $students;
    }

    /**
     * @return string[]
     */
    public function getPaymentList(Period $period, School $school): array
    {
        $queryBuilder = $this->createQueryBuilder('std')
            ->select([
                'std AS student',
                'psp',
                'per',
                'pck',
                'doc',
                'prs',
                'fam',
            ])
            ->addSelect('CASE WHEN std.enable > 0
                THEN (psp.amount - psp.discount) ELSE SUM(psp.amount) END AS amountTotal')
            ->addSelect('COUNT(pps.id) AS numberPayment')
            ->addSelect('SUM(pps.amount) AS paymentTotal')
            ->innerJoin('std.packagePeriods', 'psp')
            ->innerJoin('psp.package', 'pck')
            ->innerJoin('psp.period', 'per')
            ->leftJoin('std.person', 'prs')
            ->leftJoin('prs.family', 'fam')
            ->leftJoin('prs.image', 'doc')
            ->leftJoin('psp.payments', 'pps')
            ->leftJoin('pps.operation', 'ope')
            ->where('per.id = :period')
            ->andWhere('std.school = :school')
            ->groupBy('std.id, psp.id, per.id, pck.id, doc.id, prs.id, fam.id')
            ->setParameter('school', $school)
            ->setParameter('period', $period)
        ;

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @return string[]
     *
     * @throws AppException
     */
    public function getStatsStudent(School $school): array
    {
        $queryBuilder = $this->createQueryBuilder('std')
            ->select([
                'COUNT(std.id) + 0 AS nbTotal',
                'SUM(std.enable) + 0 AS nbActive',
                '0 + COUNT(std.id) - SUM(std.enable) AS nbDisabled',
                'SUM(CASE WHEN prs.gender = :gender_male THEN 1 ELSE 0 END) AS nbMale',
                'SUM(CASE WHEN prs.gender = :gender_female THEN 1 ELSE 0 END) AS nbFemale',
            ])
            ->innerJoin('std.person', 'prs')
            ->where('std.school = :school')
            ->setParameter('school', $school)
            ->setParameter('gender_male', Person::GENDER_MALE)
            ->setParameter('gender_female', Person::GENDER_FEMALE)
        ;

        try {
            $result = $queryBuilder->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_ARRAY)
            ;
        } catch (ORMException $ormException) {
            throw new AppException(\sprintf('%s Query failed', __FUNCTION__), throwable: $ormException);
        }

        return $result;
    }

    /**
     * @return Student[]
     */
    public function getListStudentsWithoutPackagePeriod(Period $period, School $school): array
    {
        $queryBuilder = $this->createQueryBuilder('std')
            ->select(['doc', 'std', 'prs'])
            ->innerJoin('std.person', 'prs')
            ->leftJoin('prs.image', 'doc')
            ->leftJoin('std.packagePeriods', 'psp', 'WITH', 'psp.period = :period')
            ->where('psp.id IS NULL')
            ->andWhere('std.enable = 1')
            ->andWhere('std.school = :school')
            ->setParameter('period', $period->getId())
            ->setParameter('school', $school->getId())
        ;

        return $queryBuilder->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return int[]
     */
    public function getStatsStudentRegistered(School $school, Period $period): array
    {
        return $this->getStatsMove($school, $period);
    }

    /**
     * @return int[]
     */
    public function getStatsStudentDeactivated(School $school, Period $period): array
    {
        return $this->getStatsMove($school, $period, 'dateDesactivated');
    }

    /**
     * @return Student[]
     */
    public function getListStudentsWithoutClassPeriod(Period $period, School $school): array
    {
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.classPeriods', 'cs')
            ->leftJoin('cs.classPeriod', 'c', 'WITH', 'c.period = :period')
            ->setParameter('period', $period->getId())
            ->where('cs.classPeriod IS NULL')
            ->andWhere('s.school = :school')
            ->setParameter('school', $school->getId())
            ->andWhere('s.enable = true')
            ->getQuery()
        ;

        return $query->getArrayResult();
    }

    /**
     * @return int[]
     */
    private function getStatsMove(School $school, Period $period, string $fieldDate = 'createdAt'): array
    {
        $queryBuilder = $this->createQueryBuilder('std')
            ->select('COUNT(std.id) + 0 AS nb')
            ->addSelect(\sprintf("DATE_FORMAT(std.%s, '%%Y-%%m') AS dateMonth", $fieldDate))
            ->where('std.school = :school')
            ->andWhere('std.'.$fieldDate.' BETWEEN :begin and :end')
            ->groupBy('dateMonth')
            ->orderBy('std.createdAt', 'ASC')
            ->setParameter('school', $school)
            ->setParameter('end', $period->getEnd())
            ->setParameter('begin', $period->getBegin())
        ;

        $data = $queryBuilder->getQuery()->getArrayResult();
        $result = [];

        foreach ($data as $value) {
            $result[$value['dateMonth']] = (int) $value['nb'];
        }

        return $result;
    }
}

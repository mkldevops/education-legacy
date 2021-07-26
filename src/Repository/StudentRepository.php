<?php

declare(strict_types=1);

namespace App\Repository;

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
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    use LoggerTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * @return int[]
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getStatsNumberStudent(School $school): array
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s) AS nb')
            ->where('s.school = :school')
            ->setParameter('school', $school)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get list Students.
     *
     * @param int $iPage
     * @param int $iCountPerPage
     *
     * @return Paginator<Student>
     */
    public function getStudents($iPage = 1, $iCountPerPage = 20): Paginator
    {
        $oQuery = $this->createQueryBuilder('s')
            ->orderBy('s.name', 'DESC')
            ->leftJoin('s.grade', 'g')
            ->addSelect('g')
            ->getQuery();

        $oQuery->setFirstResult(($iPage - 1) * $iCountPerPage)
            ->setMaxResults($iCountPerPage);

        return new Paginator($oQuery);
    }

    /**
     * @return Student[]
     */
    public function getListStudents(Period $period, School $school, bool $enable = true, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('std')
            ->innerJoin('std.grade', 'gra')
            ->leftJoin('std.classPeriods', 'cps')
            ->leftJoin('cps.classPeriod', 'clp', 'WITH', 'clp.period = ?1')
            ->leftJoin('clp.classSchool', 'cls')
            ->addSelect('gra')
            ->addSelect('cps')
            ->addSelect('clp')
            ->addSelect('cls')
            ->where('std.enable = ?2')
            ->andWhere('std.school = ?3')
            ->setParameter(1, $period->getId())
            ->setParameter(2, $enable)
            ->setParameter(3, $school->getId())
            ->setMaxResults($limit)
        ;

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function getPaymentList(Period $period, School $school): array
    {
        $qb = $this->createQueryBuilder('std')
            ->select([
                'std AS student',
                'psp',
                'per',
                'pck',
                'doc',
                'prs',
            ])
            ->addSelect('CASE WHEN std.enable > 0 
                THEN (psp.amount - psp.discount) ELSE SUM(ope.amount) END AS amountTotal')
            ->addSelect('COUNT(pps.id) AS numberPayment')
            ->addSelect('SUM(ope.amount) AS paymentTotal')
            ->innerJoin('std.packagePeriods', 'psp')
            ->innerJoin('psp.package', 'pck')
            ->innerJoin('psp.period', 'per')
            ->leftJoin('std.person', 'prs')
            ->leftJoin('prs.image', 'doc')
            ->leftJoin('psp.payments', 'pps')
            ->leftJoin('pps.operation', 'ope')
            ->where('per.id = :period')
            ->andWhere('std.school = :school')
            ->groupBy('std.id')
            ->setParameter('school', $school)
            ->setParameter('period', $period);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @return string[]
     *
     * @throws AppException
     */
    public function getStatsStudent(School $school): array
    {
        $qb = $this->createQueryBuilder('std')
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
            ->setParameter('gender_female', Person::GENDER_FEMALE);

        try {
            $result = $qb->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (ORMException $e) {
            throw new AppException(sprintf('%s Query failed', __FUNCTION__), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * @return Student[]
     */
    public function getListStudentsWithoutPackagePeriod(Period $period, School $school): array
    {
        $qb = $this->createQueryBuilder('std')
            ->select(['doc', 'std', 'prs'])
            ->innerJoin('std.person', 'prs')
            ->leftJoin('prs.image', 'doc')
            ->leftJoin('std.packagePeriods', 'psp', 'WITH', 'psp.period = :period')
            ->where('psp.id IS NULL')
            ->andWhere('std.enable = 1')
            ->andWhere('std.school = :school')
            ->setParameter('period', $period->getId())
            ->setParameter('school', $school->getId());

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get stats student registered.
     *
     * @param string $fieldDate
     *
     * @return int[]
     */
    private function getStatsMove(School $school, Period $period, $fieldDate = 'createdAt'): array
    {
        $qb = $this->createQueryBuilder('std')
            ->select('COUNT(std.id) + 0 AS nb')
            ->where('std.school = :school')
            ->andWhere('std.'.$fieldDate.' BETWEEN :begin and :end')
            ->groupBy('dateMonth')
            ->orderBy('std.createdAt', 'ASC')
            ->setParameter('school', $school)
            ->setParameter('end', $period->getEnd())
            ->setParameter('begin', $period->getBegin());

        if ('sqlite' === $this->_em->getConnection()->getDriver()->getDatabasePlatform()->getName()) {
            $qb->addSelect(sprintf("strftime('%%Y-%%m', std.%s) AS dateMonth", $fieldDate));
        } else {
            $qb->addSelect(sprintf("DATE_FORMAT(std.%s, '%%Y-%%m') AS dateMonth", $fieldDate));
        }

        $data = $qb->getQuery()->getArrayResult();
        $result = [];

        foreach ($data as $value) {
            $result[$value['dateMonth']] = (int) $value['nb'];
        }

        return $result;
    }

    /**
     * Get stats student registered.
     *
     * @return int[]
     */
    public function getStatsStudentRegistered(School $school, Period $period): array
    {
        return $this->getStatsMove($school, $period);
    }

    /**
     * Get stats student registered.
     *
     * @return int[]
     */
    public function getStatsStudentDeactivated(School $school, Period $period): array
    {
        return $this->getStatsMove($school, $period, 'dateDesactivated');
    }

    /**
     * Get list Students.
     *
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
            ->getQuery();

        return $query->getArrayResult();
    }
}

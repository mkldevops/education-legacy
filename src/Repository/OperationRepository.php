<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountStatement;
use App\Entity\Operation;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * Get list operations.
     *
     * @return Operation[]
     */
    public function getListOperations(Period $period, School $school, TypeOperation $typeOperation = null): array
    {
        $qb = $this->createQueryBuilder('ope', 'ope.id')
            ->innerJoin('ope.account', 'acc')
            ->where('ope.date BETWEEN :begin AND :end')
            ->andWhere('acc.structure = :structure')
            ->setParameter('begin', $period->getBegin()?->format('Y-m-d'))
            ->setParameter('end', $period->getEnd()?->format('Y-m-d 23:59:59'))
            ->setParameter('structure', $school->getStructure()->getId());

        if (!empty($typeOperation)) {
            $qb->andWhere('ope.typeOperation = :typeOperation')
                ->setParameter('typeOperation', $typeOperation);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get list opertions available to Account Statement.
     *
     * @return array
     */
    public function getAvailableToAccountStatement(AccountStatement $accountStatement)
    {
        $begin = $accountStatement->getBegin();
        $end = $accountStatement->getEnd();
        $interval = $accountStatement->getAccount()->getIntervalOperationsAccountStatement();

        $begin->modify('-' . $interval . ' days');
        $end->modify($interval . ' days');

        return $this->createQueryBuilder('ope')
            ->select(['top.name AS typeOperation', 'ope.id', 'ope.date', 'ope.name', 'ope.amount'])
            ->innerJoin('ope.typeOperation', 'top')
            ->leftJoin('ope.accountStatement', 'acs')
            ->where('ope.date BETWEEN :begin AND :end')
            ->andWhere('ope.account = :account')
            ->andWhere('acs.id is null')
            ->setParameter('begin', $accountStatement->getBegin()->format('Y-m-d'))
            ->setParameter('end', $accountStatement->getEnd()->format('Y-m-d 23:59:59'))
            ->setParameter('account', $accountStatement->getAccount()->getId())
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * stats sumCredit & sumDebit Operations To Account Statement.
     */
    public function getQueryStatsAccountStatement(array $listAccountStatementId): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('ope')
            ->select([
                'acs.id',
                'COUNT(ope.id) AS numberOperations',
                'SUM( IF( ope.amount  > 0, ope.amount, 0)) AS sumCredit',
                'SUM( IF( ope.amount  < 0, ope.amount, 0)) AS sumDebit',
            ])
            ->innerJoin('ope.accountStatement', 'acs')
            ->andWhere('ope.accountStatement in (:accountStatement)')
            ->setParameter('accountStatement', $listAccountStatementId)
            ->groupBy('acs.id');
    }

    /**
     * Get Number Without Account Statement.
     *
     * @throws AppException
     */
    public function getNumberWithoutAccountStatement(Account $account): int
    {
        try {
            $result = $this->createQueryBuilder('ope')
                ->select('COUNT(ope.id) AS nbOperations')
                ->where('ope.account = :account')
                ->setParameter('account', $account->getId())
                ->andWhere('ope.accountStatement is null')
                ->getQuery()
                ->getSingleResult(Query::HYDRATE_ARRAY);
        } catch (NoResultException | NonUniqueResultException $e) {
            throw new AppException(sprintf('%s Error on query', __FUNCTION__), $e->getCode(), $e);
        }

        return (int)$result['nbOperations'];
    }

    /**
     * Get list Students.
     *
     * @return array
     */
    public function getStatsByMonthly(Period $period, School $school)
    {
        $query = $this->createQueryBuilder('ope')
            ->select("DATE_FORMAT(ope.date, '%Y-%m-01') AS groupDate")
            ->addSelect('top.name as nameTypeOperation')
            ->addSelect('top.id AS idTypeOperation')
            ->addSelect('SUM( IF( ope.amount  > 0, ope.amount, 0)) AS sumCredit')
            ->addSelect('SUM( IF( ope.amount  < 0, ope.amount, 0)) AS sumDebit')
            ->addSelect('COUNT(ope.id) AS numberOperations')
            ->innerJoin('ope.typeOperation', 'top')
            ->innerJoin('ope.account', 'acc')
            ->where('ope.date BETWEEN :begin AND :end')
            ->andWhere('acc.principal = 1')
            ->andWhere('acc.structure = :structure')
            ->groupBy('groupDate', 'top.name')
            ->setParameter('begin', $period->getBegin()->format('Y-m-d'))
            ->setParameter('end', $period->getEnd()->format('Y-m-d 23:59:59'))
            ->setParameter('structure', $school->getStructure()->getId())
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * getDataOperationsToAccount.
     *
     * @return mixed[]
     */
    public function getDataOperationsToAccount(Account $account): array
    {
        $result = $this->createQueryBuilder('ope')
            ->innerJoin('ope.typeOperation', 'top')
            ->select('COUNT(ope.id) AS countOperations')
            ->addSelect('SUM(ope.amount) AS amount')
            ->addSelect('SUM(IF(ope.amount > 0, ope.amount, 0)) AS amountCredit')
            ->addSelect('SUM(IF(ope.amount > 0 AND top.isInternalTransfert = 1, ope.amount, 0)) AS amountITC')
            ->addSelect('SUM(IF(ope.amount < 0, ope.amount, 0)) AS amountDebit')
            ->addSelect('SUM(IF(ope.amount < 0 AND top.isInternalTransfert = 0, ope.amount, 0)) AS amountITD')
            ->where('ope.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getArrayResult();

        return current($result);
    }

    /**
     * getLastOperation.
     */
    public function getLastOperation(School $school, int $maxResult = 10): array
    {
        return $this->createQueryBuilder('ope')
            ->select('ope.id')
            ->addSelect('ope.date')
            ->addSelect('ope.datePlanned')
            ->addSelect('ope.name')
            ->addSelect('top.name AS typeOperation')
            ->addSelect('ope.amount')
            ->innerJoin('ope.typeOperation', 'top')
            ->innerJoin('ope.account', 'acc')
            ->where('acc.structure = :structure')
            ->setParameter('structure', $school->getStructure())
            ->orderBy('ope.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return Operation[]
     */
    public function search(string $search): ?array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('REGEXP(p.name, :search) = 1')
            ->orWhere('p.uniqueId = :search')
            ->orWhere('REGEXP(p.reference, :search) = 1')
            ->orWhere('REGEXP(p.comment, :search) = 1')
            ->setParameter('search', $search)
            ->setMaxResults(10);

        if (is_numeric($search)) {
            $qb->orWhere('REGEXP(p.amount, :amount) = 1')
                ->setParameter('amount', (float)str_replace(',', '.', $search));
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Operation[]
     */
    public function toValidate(Period $period) : array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.date BETWEEN :begin AND :end')
            ->andWhere('o.validate is null')
            ->setParameter('begin', $period->getBegin())
            ->setParameter('end', $period->getEnd())
            ->getQuery()
            ->getResult();
    }
}

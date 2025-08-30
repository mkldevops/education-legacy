<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Account::class);
    }

    /**
     * @return int[]
     */
    public function getStatsAccount(School $school, bool $principalOnly = true, array $listAccountId = []): array
    {
        $result = $this
            ->getAccounts($school, $principalOnly, $listAccountId)
            ->getArrayResult()
        ;

        if ([] === $result) {
            return [];
        }

        $total = ['amount' => 0, 'amountCredit' => 0, 'amountDebit' => 0];

        foreach ($result as $account) {
            $total['amount'] += $account['amount'];
            $total['amountCredit'] += $account['amountCredit'];
            $total['amountDebit'] += $account['amountDebit'];
        }

        $result['total'] = $total;

        return $result;
    }

    public function getAccounts(School $school, bool $principalOnly = true, array $listAccountId = []): Query
    {
        return $this->getAccountsQB($school, $principalOnly, $listAccountId)
            ->leftJoin('acc.operations', 'ope')
            ->leftJoin('ope.typeOperation', 'top')
            ->addSelect('acc.name')
            ->addSelect('COUNT(ope.id) AS countOperations')
            ->addSelect('SUM(ope.amount) AS amount')
            ->addSelect('SUM(CASE WHEN ope.amount > 0 THEN ope.amount ELSE 0 END) AS amountCredit')
            ->addSelect('SUM(CASE WHEN ope.amount > 0 AND top.isInternalTransfert = 1 THEN ope.amount ELSE 0 END) AS amountITC')
            ->addSelect('SUM(CASE WHEN ope.amount < 0 THEN ope.amount ELSE 0 END) AS amountDebit')
            ->addSelect('SUM(CASE WHEN ope.amount < 0 AND top.isInternalTransfert = 0 THEN ope.amount ELSE 0 END) AS amountITD')
            ->groupBy('acc.id')
            ->getQuery()
        ;
    }

    public function getAccountsQB(School $school, bool $principalOnly = true, array $listAccountId = []): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('acc')
            ->where('acc.structure = :structure')
            ->setParameter('structure', $school->getStructure())
        ;

        if ($principalOnly) {
            $queryBuilder
                ->andWhere('acc.principal in (:principal)')
                ->setParameter('principal', 1)
            ;
        }

        if ([] !== $listAccountId) {
            $queryBuilder
                ->andWhere('acc.id in (:accounts)')
                ->setParameter('accounts', $listAccountId)
            ;
        }

        return $queryBuilder;
    }
}

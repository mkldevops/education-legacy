<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountStatement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountStatement>
 */
class AccountStatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, AccountStatement::class);
    }

    public function findByDate(Account $account, \DateTimeInterface $dateTime): ?AccountStatement
    {
        try {
            $result = $this->createQueryBuilder('a')
                ->where(':date BETWEEN a.begin AND a.end')
                ->setParameter('date', $dateTime)
                ->andWhere('a.account = :account')
                ->setParameter('account', $account)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            $result = null;
        }

        return $result;
    }
}

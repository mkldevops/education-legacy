<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\AccountStatement;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class AccountStatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountStatement::class);
    }

    public function findByDate(Account $account, DateTimeInterface $dateTime): ?AccountStatement
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
        } catch (NonUniqueResultException $e) {
            $result = null;
        }

        return $result;
    }
}

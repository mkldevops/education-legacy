<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountSlip;
use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountSlip>
 */
class AccountSlipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, AccountSlip::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getAccountSlipByRefs(string $ref, Structure $structure, string $gender): ?AccountSlip
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.operationCredit', 'oc')
            ->leftJoin('a.operationDebit', 'od')
            ->where('a.reference = :ref')
            ->andWhere('a.structure = :structure')
            ->andWhere('a.gender = :gender')
            ->setParameter('ref', $ref)
            ->setParameter('structure', $structure)
            ->setParameter('gender', $gender)
        ;

        return $queryBuilder->getQuery()->getSingleResult();
    }
}

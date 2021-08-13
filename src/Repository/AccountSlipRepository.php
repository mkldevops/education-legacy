<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountSlip;
use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * AccountSlipRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccountSlipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountSlip::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getAccountSlipByRefs(string $ref, Structure $structure, string $gender): ?AccountSlip
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.operationCredit', 'oc')
            ->leftJoin('a.operationDebit', 'od')
            ->where('a.reference = :ref')
            ->andWhere('a.structure = :structure')
            ->andWhere('a.gender = :gender')
            ->setParameter('ref', $ref)
            ->setParameter('structure', $structure)
            ->setParameter('gender', $gender);

        return $qb->getQuery()->getSingleResult();
    }
}

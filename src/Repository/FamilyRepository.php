<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Family;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Family>
 */
class FamilyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Family::class);
    }

    /**
     * @return Family[]
     */
    public function findFamilies(Person $person): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.father = :person')
            ->orWhere('f.mother = :person')
            ->orWhere('f.legalGuardian = :person')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Family[]
     */
    public function search(string $search): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.mother', 'm')
            ->where('REGEXP(f.name, :search) = 1')
            ->orWhere('REGEXP(f.email, :search) = 1')
            ->orWhere('REGEXP(f.address, :search) = 1')
            ->orWhere('REGEXP(m.name, :search) = 1')
            ->orWhere('REGEXP(m.forname, :search) = 1')
            ->orWhere('REGEXP(f.email, :search) = 1')
            ->setParameter('search', $search)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}

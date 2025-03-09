<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Package::class);
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countPackages(School $school): int
    {
        return (int) $this->getAvailable($school)
            ->select('COUNT(pck.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getAvailable(School $school): QueryBuilder
    {
        return $this->createQueryBuilder('pck')
            ->where('pck.enable = 1')
            ->andWhere('pck.school = :school')
            ->setParameter('school', $school)
        ;
    }

    public function getQueryBuilder(string $search, ?School $school = null): QueryBuilder
    {
        $search = \sprintf('%%%s%%', $search);
        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.name LIKE :search')
            ->orWhere('e.description LIKE :search')
            ->orWhere('e.price LIKE :search')
            ->orWhere('e.enable LIKE :search')
            ->setParameter('search', $search)
        ;

        if ($school instanceof School) {
            $queryBuilder->andWhere('e.school = :school')
                ->setParameter('school', $school)
            ;
        }

        return $queryBuilder;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypeOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeOperation>
 */
class TypeOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, TypeOperation::class);
    }

    public function getParents(): QueryBuilder
    {
        return $this->createQueryBuilder('to')
            ->where('to.status = 1 and to.parent is null')
        ;
    }
}

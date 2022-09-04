<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypeOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|TypeOperation find($id, $lockMode = null, $lockVersion = null)
 * @method null|TypeOperation findOneBy(array $criteria, array $orderBy = null)
 * @method TypeOperation[]    findAll()
 * @method TypeOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeOperation::class);
    }

    public function getParents(): QueryBuilder
    {
        return $this->createQueryBuilder('to')
            ->where('to.status = 1 and to.parent is null')
        ;
    }
}

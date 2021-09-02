<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OperationGender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OperationGender|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationGender|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationGender[]    findAll()
 * @method OperationGender[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationGenderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OperationGender::class);
    }

    /**
     * Get period available.
     */
    public function getAvailable(): QueryBuilder
    {
        return $this->createQueryBuilder('opg')
            ->where('opg.enable = 1');
    }
}

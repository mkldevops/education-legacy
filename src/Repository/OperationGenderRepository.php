<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OperationGender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OperationGender>
 */
class OperationGenderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OperationGender::class);
    }

    /**
     * Get period available.
     */
    public function getAvailable(): QueryBuilder
    {
        return $this->createQueryBuilder('opg')
            ->where('opg.enable = 1')
        ;
    }
}

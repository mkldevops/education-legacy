<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ClassSchool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassSchool>
 */
class ClassSchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, ClassSchool::class);
    }

    /**
     * GetAvailable.
     */
    public function getQBAvailables(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.enable = 1')
        ;
    }
}

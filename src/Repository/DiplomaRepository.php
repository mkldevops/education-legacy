<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Diploma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Diploma|null find($id, $lockMode = null, $lockVersion = null)
 * @method Diploma|null findOneBy(array $criteria, array $orderBy = null)
 * @method Diploma[]    findAll()
 * @method Diploma[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiplomaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diploma::class);
    }
}

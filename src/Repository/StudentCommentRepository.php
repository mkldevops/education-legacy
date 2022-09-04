<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StudentComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|StudentComment find($id, $lockMode = null, $lockVersion = null)
 * @method null|StudentComment findOneBy(array $criteria, array $orderBy = null)
 * @method StudentComment[]    findAll()
 * @method StudentComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentComment::class);
    }
}

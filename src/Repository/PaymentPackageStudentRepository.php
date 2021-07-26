<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PaymentPackageStudent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentPackageStudent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentPackageStudent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentPackageStudent[]    findAll()
 * @method PaymentPackageStudent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentPackageStudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentPackageStudent::class);
    }
}

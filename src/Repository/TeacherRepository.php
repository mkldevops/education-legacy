<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\School;
use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Teacher|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teacher|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teacher[]    findAll()
 * @method Teacher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    public function getAvailablesQB(School $school): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.classPeriods', 'cp')
            ->innerJoin('cp.classSchool', 'cs')
            ->where('cs.school = :school')
            ->setParameter('school', $school)
            ->andWhere('t.enable = true');
    }
}

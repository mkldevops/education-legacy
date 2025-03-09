<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\School;
use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Teacher>
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Teacher::class);
    }

    public function getAvailablesQB(School $school): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.classPeriods', 'cp')
            ->innerJoin('cp.classSchool', 'cs')
            ->where('cs.school = :school')
            ->setParameter('school', $school)
            ->andWhere('t.enable = true')
        ;
    }
}

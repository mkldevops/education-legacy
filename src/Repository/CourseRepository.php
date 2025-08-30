<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ClassPeriod;
use App\Entity\Course;
use App\Entity\Period;
use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Course::class);
    }

    public function getListQueryBuilder(string $search, School $school, Period $period): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->addSelect('t')
            ->addSelect('cs')
            ->addSelect('cp')
            ->leftJoin('e.teachers', 't')
            ->innerJoin('e.classPeriod', 'cp')
            ->innerJoin('cp.classSchool', 'cs')
            ->where('cs.school = :school')
            ->setParameter('school', $school)
            ->andWhere('cp.period = :period')
            ->setParameter('period', $period->getId())
            ->andWhere('
                        e.text LIKE :text
                        OR e.comment LIKE :comment
                        OR e.hourBegin LIKE :hourBegin
                        OR e.hourEnd LIKE :hourEnd
                        OR t.name LIKE :teacher
                    ')
            ->setParameter(':text', '%'.$search.'%')
            ->setParameter(':comment', '%'.$search.'%')
            ->setParameter(':hourBegin', '%'.$search.'%')
            ->setParameter(':hourEnd', '%'.$search.'%')
            ->setParameter(':teacher', '%'.$search.'%')
            ->orderBy('e.date', 'DESC')
        ;
    }

    /**
     * @return Course[]
     */
    public function getCourseOfClass(ClassPeriod $classPeriod, \DateTimeInterface $form, int $maxResult, int $offset): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.classPeriod = :classPeriod')
            ->setParameter('classPeriod', $classPeriod)
            ->andWhere('c.date >= :from')
            ->setParameter('from', \DateTime::createFromInterface($form)->sub(new \DateInterval('P1D')))
            ->setMaxResults($maxResult)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    public function remove(ClassPeriod $classPeriod): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->delete()
            ->where('c.classPeriod = :classPeriod')
            ->setParameter('classPeriod', $classPeriod)
            ->getQuery()
            ->execute()
        ;
    }
}

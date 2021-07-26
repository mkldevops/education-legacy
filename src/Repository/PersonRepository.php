<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Family;
use App\Entity\Period;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @return Person[]
     */
    public function getPersonsToFamily(Family $family, Period $period)
    {
        return $this->createQueryBuilder('p')
            ->select(['p', 's', 'cs', 'c', 'pp', 'pck'])
            ->leftJoin('p.student', 's')
            ->leftJoin('s.classPeriods', 'cs')
            ->leftJoin('cs.classPeriod', 'c', Join::WITH, 'c.period = :period')
            ->leftJoin('s.packagePeriods', 'pp', Join::WITH, 'pp.period = :period')
            ->leftJoin('pp.package', 'pck')
            ->where('p.family = :family')
            ->setParameter('family', $family)
            ->setParameter('period', $period)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function search(string $search)
    {
        return $this->createQueryBuilder('p')
            ->where('REGEXP(p.name, :search) = 1')
            ->orWhere('REGEXP(p.forname, :search) = 1')
            ->orWhere('REGEXP(p.phone, :search) = 1')
            ->orWhere('REGEXP(p.email, :search) = 1')
            ->orWhere('REGEXP(p.address, :search) = 1')
            ->setParameter('search', preg_quote($search))
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}

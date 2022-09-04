<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ImagickException;

/**
 * @method null|Document find($id, $lockMode = null, $lockVersion = null)
 * @method null|Document findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Get list Students.
     *
     * @return array<int, array<string, mixed[]>>
     *
     * @throws ImagickException
     */
    public function last(array $exists, int $firstResult = 0, int $maxResult = 5): array
    {
        $query = $this->createQueryBuilder('doc')
            ->select('doc')
            ->where('doc.id NOT IN (:exists)')
            ->setParameter('exists', $exists)
            ->setMaxResults($maxResult)
            ->setFirstResult($firstResult)
            ->orderBy('doc.id', 'DESC')
            ->getQuery()
        ;

        $result = $query->getResult();
        $data = [];

        /** @var Document $document */
        foreach ($result as $document) {
            $data[] = ['document' => $document->getInfos()];
        }

        return $data;
    }

    /**
     * @return Document[]
     */
    public function search(string $search)
    {
        return $this->createQueryBuilder('d')
            ->where('REGEXP(d.name, :search) = 1')
            ->setParameter('search', $search)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}

<?php

declare(strict_types=1);

namespace App\Event\EventListener;

use App\Entity\AccountStatement;
use App\Entity\Operation;
use App\Repository\AccountStatementRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: 'preUpdate', method: 'preUpdate', entity: Operation::class)]
#[AsEntityListener(event: 'prePersist', method: 'prePersist', entity: Operation::class)]
readonly class OperationListener
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private AccountStatementRepository $accountStatementRepository,
    ) {}

    /**
     * @throws \Exception
     */
    public function preUpdate(Operation $operation): bool
    {
        return $this->retrieveAccountStatement($operation);
    }

    public function retrieveAccountStatement(Operation $operation): bool
    {
        $this->logger->debug(__FUNCTION__, ['operation' => $operation]);
        if (false === $operation->getAccount()?->getIsBank()) {
            return false;
        }

        if (!$operation->hasAccountStatement()) {
            $accountStatement = $this->accountStatementRepository
                ->findByDate($operation->getAccount(), $operation->getValueDate())
            ;

            $this->logger->debug(__FUNCTION__, [
                'find account statement' => $accountStatement instanceof AccountStatement,
            ]);

            if (!$accountStatement instanceof AccountStatement) {
                $begin = (clone $operation->getValueDate())->modify('first day of this month');
                $end = (clone $operation->getValueDate())->modify('last day of this month');

                $accountStatement = (new AccountStatement())
                    ->setAccount($operation->getAccount())
                    ->setMonth($begin)
                    ->setBegin($begin)
                    ->setEnd($end)
                    ->setNumberOperations(1)
                    ->addAmount($operation->getAmount())
                    ->setAuthor($operation->getAuthor())
                ;

                $this->entityManager->persist($accountStatement);
            }

            $operation->setAccountStatement($accountStatement);

            $this->logger->debug(__FUNCTION__, ['accountStatement' => $accountStatement]);
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function prePersist(Operation $operation): bool
    {
        return $this->retrieveAccountStatement($operation);
    }
}

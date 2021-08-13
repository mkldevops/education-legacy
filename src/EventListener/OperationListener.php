<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AccountStatement;
use App\Entity\Operation;
use App\Services\AbstractFullService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class OperationListener extends AbstractFullService
{
    /**
     * @throws Exception
     */
    public function preUpdate(Operation $operation, LifecycleEventArgs $args): bool
    {
        return $this->setEntityManager($args->getObjectManager())
            ->retrieveAccountStatement($operation);
    }

    public function retrieveAccountStatement(Operation $operation): bool
    {
        $this->logger->debug(__FUNCTION__, ['operation' => $operation]);
        if (!$operation->getAccount()?->getIsBank()) {
            return false;
        }

        if (!$operation->hasAccountStatement()) {
            $accountStatement = $this->entityManager
                ->getRepository(AccountStatement::class)
                ->findByDate($operation->getAccount(), $operation->getValueDate());

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
                    ->setAuthor($operation->getAuthor());

                $this->getEntityManager()->persist($accountStatement);
            }

            $operation->setAccountStatement($accountStatement);

            $this->logger->debug(__FUNCTION__, ['accountStatement' => $accountStatement]);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function prePersist(Operation $operation, LifecycleEventArgs $args): bool
    {
        return $this->setEntityManager($args->getObjectManager())
            ->retrieveAccountStatement($operation);
    }
}

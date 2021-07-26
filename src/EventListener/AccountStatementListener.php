<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AccountStatement;
use App\Services\AbstractFullService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AccountStatementListener extends AbstractFullService
{
    public function preUpdate(AccountStatement $accountStatement, LifecycleEventArgs $args): void
    {
        $this->setEntityManager($args->getObjectManager())
            ->calculate($accountStatement);
    }

    public function prePersist(AccountStatement $accountStatement, LifecycleEventArgs $args): void
    {
        $this->setEntityManager($args->getObjectManager())
            ->calculate($accountStatement);
    }

    private function calculate(AccountStatement $accountStatement): void
    {
        $accountStatement->calcNumberOperations()
            ->calcAmount();
    }
}

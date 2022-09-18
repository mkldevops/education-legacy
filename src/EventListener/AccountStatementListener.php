<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AccountStatement;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AccountStatementListener
{
    public function preUpdate(AccountStatement $accountStatement, LifecycleEventArgs $args): void
    {
        $this->calculate($accountStatement);
    }

    public function prePersist(AccountStatement $accountStatement, LifecycleEventArgs $args): void
    {
        $this->calculate($accountStatement);
    }

    private function calculate(AccountStatement $accountStatement): void
    {
        $accountStatement->calcNumberOperations()
            ->calcAmount()
        ;
    }
}

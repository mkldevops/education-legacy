<?php

declare(strict_types=1);
/**
 * PHP version: 7.1.
 *
 * @author fardus
 */

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountStatement;
use App\Entity\Operation;
use Symfony\Contracts\Service\Attribute\Required;

class AccountManager extends AccountableManager
{
    protected TransferManager $transferManager;
    protected OperationManager $operationManager;
    protected Account $account;


    public function getDataAccountStatement(Account $account) : array
    {
        $data = ['accountStatements' => [], 'nbWithoutAccountStatements' => 0];

        // if account is Bank, then show their list account statement
        if (!$account->getEnableAccountStatement()) {
            return $data;
        }

        $listAccountStatementId = [];

        /** @var AccountStatement[] $accountStatements */
        $accountStatements = [];
        $result = $this->entityManager
            ->getRepository(AccountStatement::class)
            ->findBy(['account' => $account->getId()], ['begin' => 'DESC']);

        foreach ($result as $accountStatement) {
            $id = $accountStatement->getId();
            $accountStatement->stats = [
                'numberOperations' => 0,
                'sumCredit' => 0,
                'sumDebit' => 0,
                'isValid' => false,
            ];
            $accountStatements[$id] = $accountStatement;
            $listAccountStatementId[] = $id;
        }

        $result = $this->entityManager
            ->getRepository(Operation::class)
            ->getQueryStatsAccountStatement($listAccountStatementId)
            ->getQuery()
            ->getArrayResult();

        if (!empty($result)) {
            foreach ($result as $stats) {
                $id = $stats['id'];
                $stats['isValid'] = false;

                $accountStatement = $accountStatements[$id];

                if ($accountStatement->getAmountCredit() === round((float) $stats['sumCredit'], 2)
                    && $accountStatement->getAmountDebit() === round((float) $stats['sumDebit'], 2)
                    && $accountStatement->getNumberOperations() === (int) $stats['numberOperations']) {
                    $stats['isValid'] = true;
                }

                $accountStatements[$id]->stats = $stats;
            }
        }

        $result = $this->entityManager
            ->getRepository(Operation::class)
            ->getNumberWithoutAccountStatement($account);

        $data['nbWithoutAccountStatements'] = $result['nbOperations'] ?? 0;
        $data['accountStatements'] = $accountStatements;

        return $data;
    }

    #[Required]
    public function setTransferManager(TransferManager $transferManager) : static
    {
        $this->transferManager = $transferManager;

        return $this;
    }

    #[Required]
    public function setOperationManager(OperationManager $operationManager) : static
    {
        $this->operationManager = $operationManager;

        return $this;
    }

    public function setAccount(Account $account) : static
    {
        $this->account = $account;

        return $this;
    }
}

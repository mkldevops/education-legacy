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

/**
 * Class AccountManager.
 */
class AccountManager extends AccountableManager
{
    protected TransferManager $transferManager;

    protected OperationManager $operationManager;

    protected Account $account;

    /**
     * Get Data AccountStatement.
     *
     * @return array
     */
    public function getDataAccountStatement(Account $account)
    {
        $data = ['accountStatements' => [], 'nbWithoutAccountStatements' => 0];

        // if account is Bank, then show their list account statement
        if (!$account->getEnableAccountStatement()) {
            return $data;
        }

        $listAccountStatementId = [];

        /** @var AccountStatement[] $accountStatements */
        $accountStatements = [];
        $result = $this->getEntityManager()
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

        $result = $this->getEntityManager()
            ->getRepository(Operation::class)
            ->getQueryStatsAccountStatement($listAccountStatementId)
            ->getQuery()
            ->getArrayResult();

        if (!empty($result)) {
            foreach ($result as $stats) {
                $id = $stats['id'];
                $stats['isValid'] = false;

                $accountStatement = $accountStatements[$id];

                if ($accountStatement->getAmountCredit() === round($stats['sumCredit'], 2)
                    && $accountStatement->getAmountDebit() === round($stats['sumDebit'], 2)
                    && $accountStatement->getNumberOperations() === (int)$stats['numberOperations']) {
                    $stats['isValid'] = true;
                }

                $accountStatements[$id]->stats = $stats;
            }
        }

        $result = $this->getEntityManager()
            ->getRepository(Operation::class)
            ->getNumberWithoutAccountStatement($account);

        $data['nbWithoutAccountStatements'] = $result['nbOperations'] ?? 0;
        $data['accountStatements'] = $accountStatements;

        return $data;
    }

    /**
     * Get TransferManager.
     *
     * @return TransferManager
     */
    public function getTransferManager()
    {
        return $this->transferManager;
    }

    /**
     * Set TransferManager.
     *
     * @required
     *
     * @return static
     */
    public function setTransferManager(TransferManager $transferManager)
    {
        $this->transferManager = $transferManager;

        return $this;
    }

    /**
     * Get OperationManager.
     *
     * @return OperationManager
     */
    public function getOperationManager()
    {
        return $this->operationManager;
    }

    /**
     * Set OperationManager.
     *
     * @required
     *
     * @return static
     */
    public function setOperationManager(OperationManager $operationManager)
    {
        $this->operationManager = $operationManager;

        return $this;
    }

    /**
     * Set AccountCredit.
     *
     * @return static
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }
}

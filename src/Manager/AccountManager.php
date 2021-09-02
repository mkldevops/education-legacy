<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountStatement;
use App\Exception\AppException;
use App\Repository\AccountStatementRepository;
use App\Repository\OperationRepository;

class AccountManager
{
    protected Account $account;

    public function __construct(
        private AccountStatementRepository $accountStatementRepository,
        private OperationRepository $operationRepository,
    ) {
    }

    /**
     * @return array<string, int>|array<string, mixed[]>|array<string, mixed>
     *
     * @throws AppException
     */
    public function getDataAccountStatement(Account $account): array
    {
        $data = ['accountStatements' => [], 'nbWithoutAccountStatements' => 0];

        // if account is Bank, then show their list account statement
        if (!$account->getEnableAccountStatement()) {
            return $data;
        }

        $listAccountStatementId = [];

        /** @var AccountStatement[] $accountStatements */
        $accountStatements = [];
        $result = $this->accountStatementRepository
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

        $result = $this->operationRepository
            ->getQueryStatsAccountStatement($listAccountStatementId)
            ->getQuery()
            ->getArrayResult();

        if (!empty($result)) {
            foreach ($result as $stats) {
                $id = $stats['id'];
                $stats['isValid'] = false;

                $accountStatement = $accountStatements[$id];

                if ($accountStatement->getNumberOperations() === (int) $stats['numberOperations']
                    && $accountStatement->getAmountCredit() === round((float) $stats['sumCredit'], 2)
                    && $accountStatement->getAmountDebit() === round((float) $stats['sumDebit'], 2)
                ) {
                    $stats['isValid'] = true;
                }

                $accountStatements[$id]->stats = $stats;
            }
        }

        $nbOperations = $this->operationRepository->getNumberWithoutAccountStatement($account);

        $data['nbWithoutAccountStatements'] = $nbOperations;
        $data['accountStatements'] = $accountStatements;

        return $data;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }
}

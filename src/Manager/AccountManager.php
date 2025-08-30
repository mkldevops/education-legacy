<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Account;
use App\Exception\AppException;
use App\Repository\AccountStatementRepository;
use App\Repository\OperationRepository;

class AccountManager
{
    protected Account $account;

    public function __construct(
        private readonly AccountStatementRepository $accountStatementRepository,
        private readonly OperationRepository $operationRepository,
    ) {}

    /**
     * @return array<string, int>|array<string, mixed>|array<string, mixed[]>
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

        $result = $this->accountStatementRepository
            ->findBy(['account' => $account->getId()], ['begin' => 'DESC'])
        ;

        $months = [];
        foreach ($result as $accountStatement) {
            $id = $accountStatement->getId();
            $month = [
                'accountStatement' => $accountStatement,
                'numberOperations' => 0,
                'sumCredit' => 0,
                'sumDebit' => 0,
                'isValid' => false,
            ];
            $months[$id] = $month;
            $listAccountStatementId[] = $id;
        }

        $result = $this->operationRepository
            ->getQueryStatsAccountStatement($listAccountStatementId)
            ->getQuery()
            ->getArrayResult()
        ;

        foreach ($result as $stats) {
            $id = $stats['id'];
            $stats['isValid'] = false;

            $month = $months[$id];

            if ($month['accountStatement']->getNumberOperations() === (int) $stats['numberOperations']
                && $month['accountStatement']->getAmountCredit() === round((float) $stats['sumCredit'], 2)
                && $month['accountStatement']->getAmountDebit() === round((float) $stats['sumDebit'], 2)
            ) {
                $stats['isValid'] = true;
            }

            $month = [...$month, ...$stats];
        }

        $nbOperations = $this->operationRepository->getNumberWithoutAccountStatement($account);

        $data['nbWithoutAccountStatements'] = $nbOperations;
        $data['accountStatements'] = $months;

        return $data;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }
}

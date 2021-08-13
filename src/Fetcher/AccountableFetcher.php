<?php

namespace App\Fetcher;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\OperationGender;
use App\Entity\Structure;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use App\Repository\AccountRepository;
use App\Repository\AccountSlipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;

class AccountableFetcher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
        private AccountSlipRepository  $accountSlipRepository,
        private AccountRepository      $accountRepository,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAccountSlip(string $ref, Structure $structure, string $gender): ?AccountSlip
    {
        $accountSlip = $this->accountSlipRepository
            ->getAccountSlipByRefs($ref, $structure, $gender);

        $this->logger->debug(__FUNCTION__, ['accountSlip' => $accountSlip]);

        return $accountSlip;
    }

    /**
     * @throws AppException
     */
    public function findAccount(int $id): Account
    {
        if (empty($id)) {
            throw new AppException('Id to search account is empty');
        }

        $account = $this->accountRepository->find($id);

        if (!$account instanceof Account) {
            throw new AppException('Not found account id  : ' . $id);
        }

        return $account;
    }

    public function findTypeOperationByCode(string $code): ?TypeOperation
    {
        return $this->entityManager
            ->getRepository(TypeOperation::class)
            ->findOneBy(['code' => $code]);
    }

    public function findOperationGender(string $code): ?OperationGender
    {
        return $this->entityManager
            ->getRepository(OperationGender::class)
            ->findOneBy(['code' => $code]);
    }
}

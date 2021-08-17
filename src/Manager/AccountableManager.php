<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\OperationGender;
use App\Entity\Structure;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;

abstract class AccountableManager
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAccountSlip(string $ref, Structure $structure, string $gender): ?AccountSlip
    {
        $accountSlip = $this->entityManager
            ->getRepository(AccountSlip::class)
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

        $account = $this->entityManager
            ->find(Account::class, $id);

        if (!$account instanceof Account) {
            throw new AppException('Not found account id  : ' . $id);
        }

        return $account;
    }

    /**
     * @throws AppException
     */
    public function findTypeOperationByCode(string $code): TypeOperation
    {
        $typeOperation =  $this->entityManager
            ->getRepository(TypeOperation::class)
            ->findOneBy(['code' => $code]);

        if (!$typeOperation instanceof TypeOperation) {
            throw new AppException('Not Found Type operation with code : ' . $code);
        }

        return $typeOperation;
    }

    protected function findOperationGender(string $code): ?OperationGender
    {
        return $this->entityManager
            ->getRepository(OperationGender::class)
            ->findOneBy(['code' => $code]);
    }
}

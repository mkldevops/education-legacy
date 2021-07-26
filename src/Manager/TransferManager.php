<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 23/06/2016
 * Time: 20:27
 * PHP version : 7.1.
 */

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\Operation;
use App\Entity\TypeOperation;
use App\Exception\AlreadyExistsException;
use App\Exception\AppException;
use App\Model\TransferModel;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

/**
 * Class TransferManager.
 *
 * @author   fardus <h.fahari@gmail.com>
 *
 * @category Manager
 */
class TransferManager extends AccountableManager
{
    /**
     * @var string
     */
    public const ACTION_ADD = 'add';

    /**
     * @var string
     */
    public const ACTION_EDIT = 'edit';

    private ?\App\Entity\Account $accountCredit = null;

    private ?\App\Entity\Account $accountDebit = null;

    private ?\App\Entity\AccountSlip $accountSlip = null;

    /**
     * create transfer operations.
     *
     * @param TransferModel $transferModel Object model transfer operation
     *
     * @return AccountSlip
     *
     * @throws AppException
     */
    public function createByTransferModel(TransferModel $transferModel)
    {
        $this->setAccountSlip($transferModel->getAccountSlip());
        $this->accountSlip
            ->setDate($transferModel->getDate())
            ->setUniqueId($transferModel->getUniqueId())
            ->setAmount($transferModel->getAmount())
            ->setGender($transferModel->getGender())
            ->setReference($transferModel->getReference())
            ->setAuthor($this->getUser())
            ->setGender($transferModel->getGender()->getCode())
            ->setStructure($transferModel->getStructure());

        $this
            ->setAccountCredit($transferModel->getAccountCredit())
            ->setAccountDebit($transferModel->getAccountDebit());

        try {
            $this->create();
        } catch (AlreadyExistsException | NonUniqueResultException $e) {
            $this->logger->info(__FUNCTION__.' - '.$e->getMessage());
        }

        $transferModel->getUniqueId();
        $this->setOperation(AccountSlip::TYPE_DEBIT, $this->accountDebit)
            ->setOperation(AccountSlip::TYPE_CREDIT, $this->accountCredit);

        return $this->accountSlip;
    }

    /**
     * Create an account slip with operations.
     *
     * @throws AlreadyExistsException
     * @throws NonUniqueResultException
     */
    public function create(): AccountSlip
    {
        $this->logger->debug(__FUNCTION__);
        $result = $this->findAccountSlip(
            $this->accountSlip->getReference(),
            $this->accountSlip->getStructure(),
            $this->accountSlip->getGender()
        );

        if (!empty($result)) {
            $msg = $this->trans(
                'error.already_exists_ref',
                ['%reference%' => $this->accountSlip->getReference()],
                'account_slip'
            );

            $this->setAccountSlip($result);
            throw new AlreadyExistsException($msg);
        }

        $name = $this->trans($this->accountSlip->getGender(), [], 'account_slip');
        $this->accountSlip->setName($name)
            ->setAuthor($this->getUser());

        $manager = $this->getEntityManager();
        $manager->persist($this->accountSlip);
        $manager->flush();

        return $this->accountSlip;
    }

    /**
     * @throws AppException
     */
    private function setOperation(string $type, Account $account): self
    {
        $this->logger->info(__FUNCTION__, ['type' => $type]);

        $operation = $this->findOperation($type, $account, $this->accountSlip->getUniqueId());

        $typeOperation = $this->findTypeOperationByCode(TypeOperation::TYPE_CODE_SPLIT);
        $gender = $this->findOperationGender($this->accountSlip->getGender());

        $name = mb_strtoupper($this->accountSlip->getName(), 'UTF-8');

        $operation->setComment($this->accountSlip->getComment())
            ->setName($name)
            ->setAmount($this->accountSlip->getAmount($type))
            ->setDate($this->accountSlip->getDate())
            ->setReference($this->accountSlip->getReference())
            ->setOperationGender($gender)
            ->setAuthor($this->accountSlip->getAuthor())
            ->setPublisher($this->getUser())
            ->setAccount($account)
            ->setTypeOperation($typeOperation)
            ->setUniqueId($this->accountSlip->getUniqueId());

        $this->accountSlip->setOperation($operation, $type);
        $manager = $this->getEntityManager();
        $manager->persist($operation);
        $manager->flush();

        return $this;
    }

    /**
     * @throws AlreadyExistsException
     * @throws AppException
     * @throws NonUniqueResultException
     */
    public function createByForm() : AccountSlip
    {
        $this->create();
        $this->setOperation(AccountSlip::TYPE_DEBIT, $this->accountDebit);
        $this->setOperation(AccountSlip::TYPE_CREDIT, $this->accountCredit);

        return $this->accountSlip;
    }

    /**
     * Get AccountCredit.
     */
    public function getAccountCredit(): Account
    {
        return $this->accountCredit;
    }

    public function setAccountCredit(Account $accountCredit): self
    {
        $this->accountCredit = $accountCredit;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function setAccountCreditById(int $accountCreditId): self
    {
        $accountCredit = $this->findAccount($accountCreditId);
        $this->setAccountCredit($accountCredit);

        return $this;
    }

    /**
     * Get AccountDebit.
     */
    public function getAccountDebit(): Account
    {
        return $this->accountDebit;
    }

    /**
     * Set AccountDebit.
     */
    public function setAccountDebit(Account $accountDebit): self
    {
        $this->accountDebit = $accountDebit;

        return $this;
    }

    /**
     * Set AccountDebit.
     *
     *
     * @throws Exception
     */
    public function setAccountDebitById(int $accountDebitId): self
    {
        $accountDebit = $this->findAccount($accountDebitId);
        $this->setAccountDebit($accountDebit);

        return $this;
    }

    /**
     * @return AccountSlip
     *
     * @throws AppException
     */
    public function update()
    {
        if ($this->accountSlip->hasOperationCredit()) {
            $this->setAccountCredit($this->accountSlip->getOperationCredit()->getAccount());
        }

        if ($this->accountSlip->hasOperationDebit()) {
            $this->setAccountDebit($this->accountSlip->getOperationCredit()->getAccount());
        }

        $this->setOperation(AccountSlip::TYPE_DEBIT, $this->accountDebit);
        $this->setOperation(AccountSlip::TYPE_CREDIT, $this->accountCredit);

        $this->getEntityManager()->persist($this->getAccountSlip());
        $this->getEntityManager()->flush();

        return $this->getAccountSlip();
    }

    /**
     * Get AccountSlip.
     */
    public function getAccountSlip(): AccountSlip
    {
        return $this->accountSlip;
    }

    /**
     * Set AccountSlip.
     */
    public function setAccountSlip(AccountSlip $accountSlip): self
    {
        $this->accountSlip = $accountSlip;

        return $this;
    }

    /**
     *
     * @return Operation|object|null
     *
     * @throws AppException
     * @throws Exception
     */
    private function findOperation(string $type, Account $account, string $uniqueId = null)
    {
        $operation = new Operation();

        if ($this->accountSlip->hasOperation($type)) {
            $operation = $this->accountSlip->getOperation($type);
        }

        if (!empty($uniqueId)) {
            $operationUnique = $this->getEntityManager()
                ->getRepository(Operation::class)
                ->findOneBy(['uniqueId' => $uniqueId, 'account' => $account]);

            if (!empty($operationUnique)) {
                $operation = $operationUnique;
            }
        }

        return $operation;
    }
}

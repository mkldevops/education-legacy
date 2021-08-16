<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\Operation;
use App\Entity\TypeOperation;
use App\Entity\User;
use App\Exception\AlreadyExistsException;
use App\Exception\AppException;
use App\Fetcher\AccountableFetcher;
use App\Model\TransferModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransferManager
{


    public const ACTION_ADD = 'add';
    public const ACTION_EDIT = 'edit';

    private ?Account $accountCredit = null;
    private ?Account $accountDebit = null;
    private AccountSlip $accountSlip;
    private User|UserInterface $user;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
        private AccountableFetcher     $fetcher,
        private TranslatorInterface    $translator,
        Security                       $security,
    ) {
        if (($user = $security->getUser()) instanceof User) {
            $this->user = $user;
        }
    }

    /**
     * @throws AppException
     */
    public function createByTransferModel(TransferModel $transferModel): AccountSlip
    {
        $this->setAccountSlip($transferModel->getAccountSlip());
        $this->accountSlip
            ->setDate($transferModel->getDate())
            ->setUniqueId($transferModel->getUniqueId())
            ->setAmount($transferModel->getAmount())
            ->setGender((string)$transferModel->getGender())
            ->setReference($transferModel->getReference())
            ->setAuthor($this->user)
            ->setGender($transferModel->getGender()?->getCode())
            ->setStructure($transferModel->getStructure());

        $this
            ->setAccountCredit($transferModel->getAccountCredit())
            ->setAccountDebit($transferModel->getAccountDebit());

        try {
            $this->create();
        } catch (AlreadyExistsException | NonUniqueResultException $e) {
            $this->logger->info(__FUNCTION__ . ' - ' . $e->getMessage());
        }

        $this->setOperation(AccountSlip::TYPE_DEBIT, $this->accountDebit)
            ->setOperation(AccountSlip::TYPE_CREDIT, $this->accountCredit);

        return $this->accountSlip;
    }

    /**
     * @throws AlreadyExistsException
     * @throws NonUniqueResultException
     * @throws AppException
     */
    public function create(): AccountSlip
    {
        $this->logger->debug(__FUNCTION__);
        $result = $this->fetcher->findAccountSlip(
            $this->accountSlip->getReference(),
            $this->accountSlip->getStructure(),
            $this->accountSlip->getGender()
        );

        if ($result !== null) {
            $msg = $this->translator->trans(
                'error.already_exists_ref',
                ['%reference%' => $this->accountSlip->getReference()],
                'account_slip'
            );

            $this->setAccountSlip($result);
            throw new AlreadyExistsException($msg);
        }

        $name = $this->translator->trans($this->accountSlip->getGender(), [], 'account_slip');
        $this->accountSlip->setName($name)
            ->setAuthor($this->user);

        $this->entityManager->persist($this->accountSlip);
        $this->entityManager->flush();

        return $this->accountSlip;
    }

    /**
     * @throws AppException
     */
    private function setOperation(string $type, Account $account): self
    {
        $this->logger->info(__FUNCTION__, ['type' => $type]);

        $operation = $this->findOperation($type, $account, $this->accountSlip->getUniqueId());

        $typeOperation = $this->fetcher->findTypeOperationByCode(TypeOperation::TYPE_CODE_SPLIT);
        $gender = $this->fetcher->findOperationGender($this->accountSlip->getGender());

        $name = mb_strtoupper($this->accountSlip->getName(), 'UTF-8');

        $operation->setComment($this->accountSlip->getComment())
            ->setName($name)
            ->setAmount($this->accountSlip->getAmount($type))
            ->setDate($this->accountSlip->getDate())
            ->setReference($this->accountSlip->getReference())
            ->setOperationGender($gender)
            ->setAuthor($this->accountSlip->getAuthor())
            ->setPublisher($this->user)
            ->setAccount($account)
            ->setTypeOperation($typeOperation)
            ->setUniqueId($this->accountSlip->getUniqueId());

        $this->accountSlip->setOperation($operation, $type);
        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return Operation|object|null
     *
     * @throws AppException
     * @throws Exception
     */
    private function findOperation(string $type, Account $account, string $uniqueId = null): ?\App\Entity\Operation
    {
        $operation = new Operation();

        if ($this->accountSlip->hasOperation($type)) {
            $operation = $this->accountSlip->getOperation($type);
        }

        if (!empty($uniqueId)) {
            $operationUnique = $this->entityManager
                ->getRepository(Operation::class)
                ->findOneBy(['uniqueId' => $uniqueId, 'account' => $account]);

            if (!empty($operationUnique)) {
                $operation = $operationUnique;
            }
        }

        return $operation;
    }

    /**
     * @throws AlreadyExistsException
     * @throws AppException
     * @throws NonUniqueResultException
     */
    public function createByForm(): AccountSlip
    {
        $this->create();
        if ($this->accountDebit === null) {
            throw new AppException('Account of debit is not defined');
        }
        $this->setOperation(AccountSlip::TYPE_DEBIT, $this->accountDebit);

        if ($this->accountCredit === null) {
            throw new AppException('Account of credit is not defined');
        }
        $this->setOperation(AccountSlip::TYPE_CREDIT, $this->accountCredit);

        if ($this->accountSlip === null) {
            throw new AppException('AccountSlip not added correctly');
        }

        return $this->accountSlip;
    }

    public function getAccountCredit(): ?Account
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
        $accountCredit = $this->fetcher->findAccount($accountCreditId);
        $this->setAccountCredit($accountCredit);

        return $this;
    }

    public function getAccountDebit(): ?Account
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
     * @throws Exception
     */
    public function setAccountDebitById(int $accountDebitId): self
    {
        $accountDebit = $this->fetcher->findAccount($accountDebitId);
        $this->setAccountDebit($accountDebit);

        return $this;
    }

    /**
     * @throws AppException
     */
    public function update(): AccountSlip
    {
        if (!($operation = $this->accountSlip?->getOperationCredit()) instanceof Operation
            || !($account = $operation->getAccount()) instanceof Account) {
            throw new AppException('Not found account of accountslip credit');
        }
        $this->setAccountCredit($account);
        $this->setOperation(AccountSlip::TYPE_DEBIT, $account);


        if (!($operation = $this->accountSlip?->getOperationDebit()) instanceof Operation
            || !($account = $operation->getAccount()) instanceof Account) {
            throw new AppException('Not found account of accountslip credit');
        }
        $this->setAccountDebit($account);
        $this->setOperation(AccountSlip::TYPE_CREDIT, $account);

        $this->entityManager->persist($this->getAccountSlip());
        $this->entityManager->flush();

        return $this->getAccountSlip();
    }

    /**
     * @throws AppException
     */
    public function getAccountSlip(): AccountSlip
    {
        if ($this->accountSlip === null) {
            throw new AppException('AccountSlip not defined');
        }
        return $this->accountSlip;
    }

    public function setAccountSlip(AccountSlip $accountSlip): self
    {
        $this->accountSlip = $accountSlip;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\Operation;
use App\Entity\OperationGender;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use App\Fetcher\AccountableFetcher;
use App\Model\TransferModel;
use Doctrine\ORM\EntityManagerInterface;
use Endeken\OFX\BankAccount;
use Endeken\OFX\OFX;
use Endeken\OFX\OFXData;
use Endeken\OFX\Transaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

class OFXManager
{
    /**
     * @var string
     */
    final public const STATUS_ALREADY = 'already';

    /**
     * @var string
     */
    final public const STATUS_ADD_OPERATION = 'add_operation';

    /**
     * @var string
     */
    final public const STATUS_ADD_TRANSFER = 'add_transfer';

    private Account $account;

    private ?Account $accountTransfer = null;

    private array $logs = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly OperationManager $operationManager,
        private readonly TransferManager $transferManager,
        private readonly AccountableFetcher $accountableFetcher,
    ) {}

    /**
     * @throws AppException
     */
    public function ofx(File $file): bool
    {
        set_time_limit(300);
        $ofxData = $this->extractOfxToFile($file);
        $count = 0;

        /** @var BankAccount $bank */
        foreach ($ofxData->bankAccounts as $bank) {
            if ([] === $bank->statement->transactions) {
                continue;
            }

            foreach ($bank->statement->transactions as $transaction) {
                $operation = $this->operationManager
                    ->findOperationByUniqueId($transaction->uniqueId)
                ;

                if ($operation instanceof Operation) {
                    $operation->setAmount($transaction->amount);
                    $this->entityManager->persist($operation);
                    $this->entityManager->flush();

                    if (!self::isTransfer($transaction->name)) {
                        $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ALREADY];

                        continue;
                    }
                }

                $this->transactionToOperation($transaction);
            }

            $count += \count($bank->statement->transactions);
        }

        return \count($this->logs) === $count;
    }

    /**
     * @throws AppException
     * @throws \Exception
     */
    public function extractOfxToFile(File $file): OFXData
    {
        if (empty($file->getRealPath())) {
            throw new AppException('The file was not path');
        }

        $content = self::uniformizeContent(file_get_contents($file->getRealPath()));

        $parsedData = OFX::parse($content);
        if (!$parsedData instanceof OFXData) {
            throw new AppException('Failed to parse OFX file');
        }

        return $parsedData;
    }

    public static function uniformizeContent(string $content): ?string
    {
        return preg_replace("#<TRNAMT>([\\-]?\\d+)\n<FITID>#", "<TRNAMT>$1.00\n<FITID>", $content);
    }

    public static function isTransfer(string $txt): bool
    {
        return (bool) preg_match('#REMISE CHEQUE|VERSEMENT|VRST|RETRAIT#i', $txt);
    }

    public function getLogs(): ?array
    {
        return $this->logs;
    }

    public function getAccountTransfer(): ?Account
    {
        return $this->accountTransfer;
    }

    public function setAccountTransfer(Account $account): static
    {
        $this->accountTransfer = $account;

        return $this;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    protected function getGenderByOFX(Transaction $transaction): ?OperationGender
    {
        $listLabels = [
            'carte' => OperationGender::CODE_CB,
            'cheque' => OperationGender::CODE_CHEQUE,
            'remise cheque' => OperationGender::CODE_REMISE,
            'vir' => OperationGender::CODE_VIR,
            'cotisation' => OperationGender::CODE_PRLVT,
            'prelevement' => OperationGender::CODE_PRLVT,
            'versement' => OperationGender::CODE_VRSMT,
            'vrst gab' => OperationGender::CODE_VRSMT,
            'frais ret' => OperationGender::CODE_PRLVT,
        ];

        $pattern = '#(?<gender>'.implode('|', array_keys($listLabels)).')#i';
        if (!preg_match($pattern, strtolower($transaction->name), $matches)) {
            $this->logger->error(__FUNCTION__.' Not found gender on ofx type : "'.$transaction->name.'"');

            return null;
        }

        $genderCode = $listLabels[$matches['gender']];

        $gender = $this->entityManager
            ->getRepository(OperationGender::class)
            ->findOneBy(['code' => $genderCode])
        ;

        if (!$gender instanceof OperationGender) {
            $this->logger->error(__FUNCTION__.' Nothing found gender with code : '.$genderCode);
        }

        return $gender;
    }

    protected static function getReference(Transaction $transaction): string
    {
        $text = trim((string) $transaction->name).' '.trim((string) $transaction->memo);
        $text = preg_replace('# +#', ' ', $text);
        if (preg_match('#(?<reference>\d+)#', (string) $text, $matches)) {
            return $matches['reference'];
        }

        return '';
    }

    /**
     * @throws AppException
     */
    private function transactionToOperation(Transaction $transaction): void
    {
        $transaction->memo = trim($transaction->memo);
        $gender = $this->getGenderByOFX($transaction);
        $reference = self::getReference($transaction);

        if (self::isTransfer($transaction->name)) {
            $this->transferOperation($transaction, $reference, $gender);
        } else {
            $type = $this->accountableFetcher->findTypeOperationByCode(TypeOperation::TYPE_CODE_TO_DEFINE);

            $operation = OperationManager::createOperationOfx($transaction)
                ->setReference($reference)
                ->setAccount($this->account)
                ->setUniqueId($transaction->uniqueId)
                ->setTypeOperation($type)
                ->setOperationGender($gender)
            ;

            try {
                $this->entityManager->persist($operation);
                $this->entityManager->flush();
                $this->logger->info(__FUNCTION__.' Operation is saved');
                $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ADD_OPERATION];
            } catch (\Exception $exception) {
                $this->logger->error(__FUNCTION__.' '.$exception->getMessage());

                throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
            }
        }
    }

    /**
     * @throws AppException
     */
    private function transferOperation(
        Transaction $transaction,
        string $reference,
        ?OperationGender $operationGender
    ): ?Operation {
        $transferModel = (new TransferModel())
            ->setAccountSlip(new AccountSlip())
            ->setAccountCredit($this->account)
            ->setAccountDebit($this->accountTransfer)
            ->setStructure($this->account->getStructure())
            ->setReference($reference)
            ->setComment($transaction->memo)
            ->setDate($transaction->date)
            ->setUniqueId($transaction->uniqueId)
            ->setAmount($transaction->amount)
            ->setGender($operationGender)
        ;

        $accountSlip = $this->transferManager->createByTransferModel($transferModel);

        if ($accountSlip->getOperationCredit()?->getAccount() === $this->account) {
            $operation = $accountSlip->getOperationCredit();
        } else {
            $operation = $accountSlip->getOperationDebit();
        }

        $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ADD_TRANSFER];

        return $operation;
    }
}

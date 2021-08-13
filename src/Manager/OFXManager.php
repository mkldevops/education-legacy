<?php

declare(strict_types=1);
/**
 * PHP version: 7.1.
 *
 * @author fardus
 */

namespace App\Manager;

use App\Entity\Account;
use App\Entity\AccountSlip;
use App\Entity\Operation;
use App\Entity\OperationGender;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use App\Model\TransferModel;
use App\Services\GoogleDriveService;
use Exception;
use OfxParser\Entities\Transaction;
use OfxParser\Ofx;
use OfxParser\Parser;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class OFXManager.
 */
class OFXManager extends AccountManager
{
    public const STATUS_ALREADY = 'already';
    public const STATUS_ADD_OPETION = 'add_operation';
    public const STATUS_ADD_TRANSFER = 'add_transfer';

    private ?Account $accountTransfer = null;

    private ?array $logs = null;

    /**
     * ofx.
     *
     * @return bool
     *
     * @throws AppException
     */
    public function ofx(File $fileUpload)
    {
        set_time_limit(300);
        $ofx = $this->extractOfxToFile($fileUpload);

        foreach ($ofx->bankAccounts as $bank) {
            if (!empty($bank->statement->transactions)) {
                foreach ($bank->statement->transactions as $transaction) {
                    $operation = $this->operationManager
                        ->findOperationByUniqueId($transaction->uniqueId);

                    if (!empty($operation)) {
                        $operation->setAmount($transaction->amount);
                        $this->getEntityManager()->persist($operation);
                        $this->getEntityManager()->flush();

                        if (!self::isTransfer($transaction->name)) {
                            $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ALREADY];
                            continue;
                        }
                    }

                    $this->transactionToOperation($transaction);
                }
            }
        }

        return count($this->getLogs()) === count($bank->statement->transactions);
    }

    /**
     * Extract Ofx To File.
     *
     * @return Ofx
     *
     * @throws AppException
     * @throws Exception
     */
    public function extractOfxToFile(File $fileUpload)
    {
        if (empty($fileUpload->getRealPath())) {
            throw new AppException('The file was not path');
        }

        $content = self::uniformizeContent(file_get_contents($fileUpload->getRealPath()));
        $ofxParser = new Parser();

        return $ofxParser->loadFromString($content);
    }

    /**
     * @return string|string[]|null
     */
    public static function uniformizeContent(string $content)
    {
        return preg_replace("#<TRNAMT>([\-]?\d+)\n<FITID>#", "<TRNAMT>$1.00\n<FITID>", $content);
    }

    /**
     * @return false|int
     */
    public static function isTransfer(string $txt)
    {
        return preg_match('#REMISE CHEQUE|VERSEMENT|VRST|RETRAIT#i', $txt);
    }

    /**
     * @return Operation|null
     *
     * @throws AppException
     */
    private function transactionToOperation(Transaction $transaction)
    {
        $transaction->memo = trim($transaction->memo);
        $gender = $this->getGenderByOFX($transaction);
        $reference = self::getReference($transaction);

        if (self::isTransfer($transaction->name)) {
            $operation = $this->transferOperation($transaction, $reference, $gender);
        } else {
            $type = $this->findTypeOperationByCode(
                TypeOperation::TYPE_CODE_TO_DEFINE
            );

            $operation = OperationManager::createOperationOfx($transaction)
                ->setReference($reference)
                ->setAccount($this->account)
                ->setUniqueId($transaction->uniqueId)
                ->setTypeOperation($type)
                ->setAuthor($this->getUser())
                ->setPublisher($this->getUser())
                ->setOperationGender($gender);

            try {
                $this->getEntityManager()->persist($operation);
                $this->getEntityManager()->flush();
                $this->logger->info(__FUNCTION__ . ' Operation is saved');
                $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ADD_OPETION];
            } catch (Exception $e) {
                $this->logger->error(__FUNCTION__ . ' ' . $e->getMessage());
            }
        }

        return $operation;
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

        $pattern = '#(?<gender>' . implode('|', array_keys($listLabels)) . ')#i';
        if (!preg_match($pattern, strtolower($transaction->name), $matches)) {
            $this->logger->error(__FUNCTION__ . ' Not found gender on ofx type : "' . $transaction->name . '"');

            return null;
        }

        $genderCode = $listLabels[$matches['gender']];

        $gender = $this->entityManager
            ->getRepository(OperationGender::class)
            ->findOneBy(['code' => $genderCode]);

        if (empty($gender)) {
            $this->logger->error(__FUNCTION__ . ' Nothing found gender with code : ' . $genderCode);
        }

        return $gender;
    }

    /**
     * Get Reference extract on text ofx.
     */
    protected static function getReference(Transaction $transaction): ?string
    {
        $reference = null;
        $text = trim($transaction->name) . ' ' . trim($transaction->memo);
        $text = preg_replace('# +#', ' ', $text);

        if (preg_match('#(?<reference>\d+)#', $text, $mathes)) {
            $reference = $mathes['reference'];
        } else {
            dump(__FUNCTION__ . ' not found :' . $text);
        }

        return $reference;
    }

    /**
     * @return Operation|null
     *
     * @throws AppException
     * @throws Exception
     */
    private function transferOperation(Transaction $transaction, ?string $reference, ?OperationGender $gender)
    {
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
            ->setGender($gender);

        $accountSlip = $this->transferManager
            ->createByTransferModel($transferModel);

        if ($accountSlip->getOperationCredit()->getAccount() === $this->account) {
            $operation = $accountSlip->getOperationCredit();
        } else {
            $operation = $accountSlip->getOperationDebit();
        }

        $this->logs[] = ['operation' => $operation, 'status' => self::STATUS_ADD_TRANSFER];

        return $operation;
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return OFXManager
     */
    public function setLogs(array $logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * getAccountTransfer.
     *
     * @return Account
     */
    public function getAccountTransfer()
    {
        return $this->accountTransfer;
    }

    /**
     * @return OFXManager
     */
    public function setAccountTransfer(Account $accountTransfer)
    {
        $this->accountTransfer = $accountTransfer;

        return $this;
    }

    /**
     * setGoogleDrive.
     *
     * @return OFXManager
     */
    public function setGoogleDrive(GoogleDriveService $googleDrive)
    {
        return $this;
    }
}

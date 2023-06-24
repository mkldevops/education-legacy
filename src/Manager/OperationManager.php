<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OfxParser\Entities\Transaction;
use Psr\Log\LoggerInterface;

class OperationManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly OperationRepository $repository,
        private readonly PeriodManager $periodManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getData(Operation $operation): array
    {
        return [
            'typeOperation' => [
                'id' => $operation->getTypeOperation()?->getId(),
                'name' => $operation->getTypeOperation()?->getName(),
            ],
            'date' => $operation->getDate()?->getTimestamp(),
            'datePlanned' => $operation->getDatePlanned()?->getTimestamp(),
            'amount' => $operation->getAmount(),
        ];
    }

    public static function createOperationOfx(Transaction $transaction): Operation
    {
        return (new Operation())
            ->setName($transaction->name)
            ->setAmount($transaction->amount)
            ->setDate($transaction->date)
            ->setComment($transaction->memo)
        ;
    }

    /**
     * @throws AppException
     */
    public function findOperationByUniqueId(int $uniqueId): ?Operation
    {
        if (empty($uniqueId)) {
            throw new AppException('Unique id is empty');
        }

        try {
            $operation = $this->repository->findOneBy(['uniqueId' => $uniqueId]);
        } catch (Exception $exception) {
            $this->logger->error(__FUNCTION__.' '.$exception->getMessage());

            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $operation;
    }

    /**
     * @todo use BaseManager
     */
    public function update(Operation $operation): bool
    {
        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return Operation[]
     *
     * @throws AppException
     * @throws InvalidArgumentException
     */
    public function toValidate(): array
    {
        return $this->repository->toValidate($this->periodManager->getPeriodsOnSession());
    }
}

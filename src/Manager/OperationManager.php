<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Entity\Period;
use App\Exception\AppException;
use App\Fetcher\AccountableFetcher;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OfxParser\Entities\Transaction;
use Psr\Log\LoggerInterface;

class OperationManager
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        protected OperationRepository $repository,
    ) {
    }

    /**
     * @return array<string, mixed[]>
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
            ->setComment($transaction->memo);
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
        } catch (Exception $e) {
            $this->logger->error(__FUNCTION__ . ' ' . $e->getMessage());
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $operation;
    }

    /**
     * @throws AppException
     */
    public function update(Operation $operation, array $data): bool
    {
        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);

            if (is_array($value) && (!empty($value['class']) && !empty($value['id']))) {
                $value = $this->findEntity($value['class'], $value['id']);
            }

            if (method_exists($operation, $method)) {
                call_user_func([$operation, $method], $value);
            }
        }

        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @throws AppException
     */
    private function findEntity(string $class, int $id): ?object
    {
        $result = $this->entityManager
            ->getRepository($class)
            ->find($id);

        if (empty($result)) {
            throw new AppException('Not found entity ' . $class . ' with id ' . $id);
        }

        return $result;
    }

    /**
     * @return Operation[]
     */
    public function toValidate(Period $period): array
    {
        return $this->repository->toValidate($period);
    }
}

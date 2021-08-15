<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Entity\Period;
use App\Exception\AppException;
use Exception;
use OfxParser\Entities\Transaction;


class OperationManager extends AccountableManager
{

    public static function getData(Operation $operation) : array
    {
        return [
            'typeOperation' => [
                'id' => $operation->getTypeOperation()->getId(),
                'name' => $operation->getTypeOperation()->getName(),
            ],
            'date' => $operation->getDate()->getTimestamp(),
            'datePlanned' => $operation->getDatePlanned()->getTimestamp(),
            'amount' => $operation->getAmount(),
        ];
    }

    public static function createOperationOfx(Transaction $transaction) : Operation
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
    public function findOperationByUniqueId(int $uniqueId) : ?Operation
    {
        if (empty($uniqueId)) {
            throw new AppException('Unique id is empty');
        }

        $operation = null;
        try {
            $operation = $this->entityManager
                ->getRepository(Operation::class)
                ->findOneBy(['uniqueId' => $uniqueId]);
        } catch (Exception $e) {
            $this->logger->error(__FUNCTION__ . ' ' . $e->getMessage());
        }

        return $operation;
    }

    /**
     * @throws AppException
     */
    public function update(Operation $operation, array $data) : bool
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
     * @return object|null
     *
     * @throws AppException
     */
    private function findEntity(string $class, int $id)
    {
        $result = $this->entityManager
            ->getRepository($class)
            ->find($id);

        if (empty($result)) {
            throw new AppException('Not found entity ' . $class . ' with id ' . $id);
        }

        return $result;
    }

    public function toValidate(Period $period): void
    {
        $this->entityManager->getRepository(Operation::class)
            ->toValidate($period);
    }
}

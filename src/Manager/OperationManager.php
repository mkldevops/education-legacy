<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Entity\Period;
use App\Exception\AppException;
use Exception;
use OfxParser\Entities\Transaction;

/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 13/05/2016
 * Time: 22:13
 * PHP version : 7.1.
 *
 * @author fardus <h.fahari@gmail.com>
 */
class OperationManager extends AccountableManager
{
    /**
     * getData.
     *
     * @return array
     */
    public static function getData(Operation $operation)
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

    /**
     * create Operation with Transaction of ofx.
     *
     * @return Operation
     */
    public static function createOperationOfx(Transaction $transaction)
    {
        return (new Operation())
            ->setName($transaction->name)
            ->setAmount($transaction->amount)
            ->setDate($transaction->date)
            ->setComment($transaction->memo);
    }

    /**
     * Find Operation By UniqueId.
     *
     * @param int $uniqueId
     *
     * @return Operation|null
     *
     * @throws AppException
     */
    public function findOperationByUniqueId($uniqueId)
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
     * Update operation.
     *
     * @return bool
     *
     * @throws AppException
     */
    public function update(Operation $operation, array $data)
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

        $manager = $this->getEntityManager();
        $manager->persist($operation);
        $manager->flush();

        return true;
    }

    /**
     * @return object|null
     *
     * @throws AppException
     */
    private function findEntity(string $class, int $id)
    {
        $result = $this->getEntityManager()
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

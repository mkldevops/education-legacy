<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Entity\Validate;

/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 13/05/2016
 * Time: 22:13
 * PHP version : 7.1.
 *
 * @author fardus <h.fahari@gmail.com>
 */
class ValidateOperationManager extends AccountableManager
{
    /**
     * @return Validate
     *
     * @throws \Exception
     *
     * @param \DateTime|\DateTimeImmutable $operationDate
     */
    public function validate(Operation $operation, \DateTimeInterface $operationDate = null)
    {
        if (!empty($operationDate)) {
            $operation->setDate(\DateTime::createFromFormat('d/m/Y', $operationDate), true);
        }

        $validate = new Validate();
        $validate->setAuthor($this->getUser());
        $validate->setType(Validate::TYPE_SUCCESS);
        $validate->setMessage($this->trans(
            'operation.validate.title',
            [
            '%id%' => $operation->getId(),
            '%name%' => $operation->getName(),
            ],
            'operation'
        ));

        $this->entityManager->persist($validate);

        $operation->setValidate($validate);

        $this->entityManager->persist($operation);

        $this->entityManager->flush();

        return $validate;
    }
}

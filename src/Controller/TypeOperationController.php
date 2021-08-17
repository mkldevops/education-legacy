<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Operation;
use App\Entity\TypeOperation;
use App\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TypeOperation controller.
 */
#[Route(path: '/type-operation')]
class TypeOperationController extends AbstractBaseController
{
    /**
     * Finds and displays a TypeOperation entity.
     *
     *
     * @throws InvalidArgumentException
     */
    #[Route(path: '/operations/{id}', name: 'app_type_operation_operations', methods: ['GET'])]
    public function operations(TypeOperation $typeOperation) : Response
    {
        $operations = $this->getManager()
            ->getRepository(Operation::class)
            ->getListOperations($this->getPeriod(), $this->getSchool(), $typeOperation);
        return $this->render('type_operation/operations.html.twig', [
            'typeoperation' => $typeOperation,
            'operations' => $operations,
        ]);
    }
    /**
     * modalList.
     */
    public function modalList(): Response
    {
        $list = $this->getDoctrine()
            ->getRepository(TypeOperation::class)
            ->findAll();

        return $this->render('type_operation/modal_list.html.twig', [
            'list' => $list,
        ]);
    }
}

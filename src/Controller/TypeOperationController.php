<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TypeOperation;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use App\Repository\OperationRepository;
use App\Repository\TypeOperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypeOperationController extends AbstractController
{
    /**
     * Finds and displays a TypeOperation entity.
     *
     * @throws AppException|InvalidArgumentException
     */
    #[Route(path: '/type-operation/operations/{id}', name: 'app_type_operation_operations', methods: ['GET'])]
    public function operations(
        TypeOperation $typeOperation,
        OperationRepository $operationRepository,
        PeriodManager $periodManager,
        SchoolManager $schoolManager
    ): Response {
        $operations = $operationRepository
            ->getListOperations($periodManager->getPeriodsOnSession(), $schoolManager->getSchool(), $typeOperation)
        ;

        return $this->render('type_operation/operations.html.twig', [
            'typeoperation' => $typeOperation,
            'operations' => $operations,
        ]);
    }

    public function modalList(TypeOperationRepository $typeOperationRepository): Response
    {
        $list = $typeOperationRepository->findAll();

        return $this->render('type_operation/modal_list.html.twig', [
            'list' => $list,
        ]);
    }
}

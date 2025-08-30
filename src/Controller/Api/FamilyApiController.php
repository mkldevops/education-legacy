<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Family;
use App\Exception\AppException;
use App\Form\FamilyType;
use App\Manager\Interfaces\FamilyManagerInterface;
use App\Model\ResponseModel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FamilyApiController extends AbstractController
{
    public function __construct(private readonly LoggerInterface $logger) {}

    /**
     * @throws AppException
     */
    #[Route(path: '/api/family/create', name: 'app_api_family_create', methods: ['POST'])]
    public function create(Request $request, FamilyManagerInterface $familyManager): JsonResponse
    {
        $this->logger->info(__FUNCTION__);

        $family = new Family();
        $form = $this->createCreateForm($family)
            ->handleRequest($request)
        ;

        $familyManager->persistData($family, $form);

        return $this->json(new ResponseModel(
            success: true,
            message: 'The family has been added.',
            data: ['id' => $family->getId(), 'label' => $family->__toString(), 'family' => $family]
        ));
    }

    public function createCreateForm(Family $family): FormInterface
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_api_family_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create']);

        return $form;
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/api/family/update/{id}', name: 'app_api_family_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Family $family, FamilyManagerInterface $familyManager): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $form = $this->createCreateForm($family)
            ->handleRequest($request)
        ;

        $familyManager->persistData($family, $form);

        return $this->json(new ResponseModel(
            success: true,
            message: 'The family has been updated.',
            data: ['family' => $family]
        ));
    }

    public function createEditForm(?Family $family = null): FormInterface
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_api_family_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.edit']);

        return $form;
    }
}

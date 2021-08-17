<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\Base\AbstractBaseController;
use App\Entity\Family;
use App\Form\FamilyType;
use App\Model\ResponseModel;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

#[Route(path: '/api/family', options: ['expose' => true])]
class FamilyApiController extends AbstractBaseController
{
    #[Route(path: '/create', name: 'app_api_family_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $result = new ResponseModel();
        try {
            $family = new Family();
            $form = $this->createCreateForm($family)
                ->handleRequest($request);

            $this->persistData($family, $form);

            $result->setSuccess(true)
                ->setMessage('The family has been added.')
                ->setData(['id' => $family->getId(), 'label' => $family->__toString()]);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $result->setMessage($e->getMessage())
                ->setSuccess(false);
        }
        return ResponseModel::jsonResponse($result);
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
     * @throws Exception
     */
    private function persistData(Family $family, FormInterface $form): void
    {
        if (!$form->isSubmitted()) {
            throw new Exception('The form is not submitted ');
        }

        if (!$form->isValid()) {
            throw new Exception('The form is not valid ' . $form->getErrors());
        }

        $em = $this->getDoctrine()->getManager();

        $family
            ->setName($family->__toString())
            ->setGenders()
            ->setEnable(true)
            ->setAuthor($this->getUser());
        $em->persist($family);
        $em->flush();
    }
    #[Route(path: '/update/{id}', name: 'app_api_family_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Family $family, Serializer $serializer): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $result = new ResponseModel();
        try {
            $form = $this->createCreateForm($family)
                ->handleRequest($request);

            $this->persistData($family, $form);

            $result->setSuccess(true)
                ->setMessage('The family has been updated.')
                ->setData(['family' => $serializer->serialize($family, 'json')]);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $result->setMessage($e->getMessage())
                ->setSuccess(false);
        }
        return ResponseModel::jsonResponse($result);
    }
    public function createEditForm(Family $family = null): FormInterface
    {
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_api_family_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.edit']);

        return $form;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\Base\AbstractBaseController;
use App\Entity\PackageStudentPeriod;
use App\Exception\AppException;
use App\Form\PackageStudentPeriodType;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/pachage-student-period', options: ['expose' => true])]
class PackageStudentPeriodApiController extends AbstractBaseController
{
    #[Route(path: '/create', name: 'app_api_package_student_period_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $response = $this->json([]);
        try {
            $packageStudentPeriod = (new PackageStudentPeriod())
                ->setPeriod($this->getEntityPeriod())
                ->setDateExpire($this->getPeriod()->getEnd());

            $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
                ->handleRequest($request);

            $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()->getPrice());
            $this->persistData($packageStudentPeriod, $form);

            $this->addFlash('success', 'The package of student has been added.');
            $response->setData(json_encode($packageStudentPeriod));
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setData(['message' => $e->getMessage()])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }
    /**
     * @throws AppException
     */
    private function persistData(PackageStudentPeriod $packageStudentPeriod, FormInterface $form): void
    {
        $this->logger->debug(__METHOD__, ['packageStudentPeriod' => $packageStudentPeriod]);
        if (!$form->isSubmitted()) {
            throw new AppException('The form is not submitted ');
        }

        if (!$form->isValid()) {
            throw new AppException('The form is not valid ' . $form->getErrors());
        }

        $em = $this->getDoctrine()->getManager();

        $packageStudentPeriod
            ->setAuthor($this->getUser());

        dump($packageStudentPeriod);
        $em->persist($packageStudentPeriod);
        $em->flush();
    }
    #[Route(path: '/update/{id}', name: 'app_api_package_student_period_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, PackageStudentPeriod $packageStudentPeriod): JsonResponse
    {
        $this->logger->info(__FUNCTION__, ['request' => $request]);
        $response = $this->json([]);
        try {
            $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
                ->handleRequest($request);

            $this->persistData($packageStudentPeriod, $form);

            $this->addFlash('success', sprintf('The package of student %s has been updated.', $packageStudentPeriod->getStudent()));
            $response->setData(json_encode($packageStudentPeriod));
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setData(['message' => $e->getMessage()])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }
}

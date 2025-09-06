<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\PackageStudentPeriod;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Form\PackageStudentPeriodType;
use App\Manager\PackageStudentPeriodManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'app_api_', options: ['expose' => true])]
class PackageStudentPeriodApiController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PackageStudentPeriodManager $packageStudentPeriodManager,
    ) {}

    /**
     * @throws AppException
     * @throws PeriodException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/pachage-student-period/create', name: 'package_student_period_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $packageStudentPeriod = new PackageStudentPeriod();

        $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)->handleRequest($request);

        $this->packageStudentPeriodManager->add($packageStudentPeriod);

        $this->addFlash('success', 'The package of student has been added.');

        return $this->json([
            'result' => true,
            'package_student_period_id' => $packageStudentPeriod->getId(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/pachage-student-period/update/{id}', name: 'package_student_period_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, PackageStudentPeriod $packageStudentPeriod): JsonResponse
    {
        $this->logger->info(__FUNCTION__, ['request' => $request]);
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
            ->handleRequest($request)
        ;

        if (!$form->isSubmitted()) {
            throw new AppException('The form is not submitted ');
        }

        if (!$form->isValid()) {
            throw new AppException('The form is not valid '.$form->getErrors());
        }

        $this->packageStudentPeriodManager->edit($packageStudentPeriod);

        $this->addFlash('success', \sprintf(
            'The package of student %s has been updated.',
            $packageStudentPeriod->getStudent()
        ));

        return $this->json([
            'result' => true,
            'package_student_period_id' => $packageStudentPeriod->getId(),
        ]);
    }
}

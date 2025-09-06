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
    #[Route(path: '/api/pachage-student-period/create', name: 'app_api_package_student_period_create', methods: ['POST'], options: ['expose' => true])]
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
    #[Route(
        path: '/api/pachage-student-period/update/{id}',
        name: 'app_api_package_student_period_update',
        methods: ['POST', 'PUT'],
        options: ['expose' => true]
    )]
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

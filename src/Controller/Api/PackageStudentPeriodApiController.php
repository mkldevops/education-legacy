<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\PackageStudentPeriodCreateDto;
use App\Dto\PackageStudentPeriodUpdateDto;
use App\Entity\PackageStudentPeriod;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Fetcher\SessionFetcherInterface;
use App\Manager\PackageStudentPeriodManager;
use App\Repository\PackageRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PackageStudentPeriodApiController extends AbstractController
{
    public function __construct(
        private readonly PackageStudentPeriodManager $packageStudentPeriodManager,
        private readonly PackageRepository $packageRepository,
        private readonly StudentRepository $studentRepository,
        private readonly SessionFetcherInterface $sessionFetcher,
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * @throws AppException
     * @throws PeriodException
     * @throws InvalidArgumentException
     */
    #[Route(
        path: '/api/pachage-student-period/create',
        name: 'app_api_package_student_period_create',
        methods: ['POST'],
        options: ['expose' => true]
    )]
    public function create(#[MapRequestPayload] PackageStudentPeriodCreateDto $packageStudentPeriodCreateDto): JsonResponse
    {
        $constraintViolationList = $this->validator->validate($packageStudentPeriodCreateDto);
        if (\count($constraintViolationList) > 0) {
            $errors = [];
            foreach ($constraintViolationList as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new AppException('Validation failed: '.json_encode($errors));
        }

        $package = $this->packageRepository->find($packageStudentPeriodCreateDto->packageId);
        $student = $this->studentRepository->find($packageStudentPeriodCreateDto->studentId);

        $period = $this->sessionFetcher->getEntityPeriodOnSession();

        $packageStudentPeriod = new PackageStudentPeriod();
        $packageStudentPeriod->setPackage($package);
        $packageStudentPeriod->setStudent($student);
        $packageStudentPeriod->setPeriod($period);
        $packageStudentPeriod->setDiscount($packageStudentPeriodCreateDto->discount);
        $packageStudentPeriod->setComment($packageStudentPeriodCreateDto->comment);

        $this->packageStudentPeriodManager->add($packageStudentPeriod);

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
    public function update(#[MapRequestPayload] PackageStudentPeriodUpdateDto $packageStudentPeriodUpdateDto, PackageStudentPeriod $packageStudentPeriod): JsonResponse
    {
        $constraintViolationList = $this->validator->validate($packageStudentPeriodUpdateDto);
        if (\count($constraintViolationList) > 0) {
            $errors = [];
            foreach ($constraintViolationList as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new AppException('Validation failed: '.json_encode($errors));
        }

        $package = $this->packageRepository->find($packageStudentPeriodUpdateDto->packageId);
        $packageStudentPeriod->setPackage($package);
        $packageStudentPeriod->setDiscount($packageStudentPeriodUpdateDto->discount);
        $packageStudentPeriod->setComment($packageStudentPeriodUpdateDto->comment);

        $this->packageStudentPeriodManager->edit($packageStudentPeriod);

        return $this->json([
            'result' => true,
            'package_student_period_id' => $packageStudentPeriod->getId(),
        ]);
    }
}

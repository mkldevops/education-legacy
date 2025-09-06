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
    /**
     * PackageStudentPeriodApiController constructor.
     *
     * Initializes the controller with its required services via dependency injection.
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PackageStudentPeriodManager $packageStudentPeriodManager,
    ) {}

    /**
     * Create a new PackageStudentPeriod from the request data and persist it.
     *
     * Accepts POST form data (handled by PackageStudentPeriodType), creates and saves
     * a new PackageStudentPeriod entity, adds a success flash message, and returns
     * a JSON response with the created entity's id.
     *
     * @return JsonResponse JSON payload: { "result": true, "package_student_period_id": int|null }
     *
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
     * Update an existing PackageStudentPeriod from a submitted form and persist the changes.
     *
     * The method expects a form submission bound to the provided PackageStudentPeriod entity.
     * If the form is submitted and valid, the entity is saved, a success flash message is added,
     * and a JSON response is returned containing the operation result and the entity id.
     *
     * @param PackageStudentPeriod $packageStudentPeriod The PackageStudentPeriod entity to update (route-injected).
     * @return JsonResponse JSON with keys:
     *                        - "result" (bool) whether the update succeeded,
     *                        - "package_student_period_id" (int|null) the entity id.
     * @throws AppException If the form is not submitted or is invalid.
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

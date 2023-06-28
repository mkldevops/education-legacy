<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ClassPeriod;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\SessionFetcher;
use App\Manager\ClassPeriodManager;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/class-period')]
class ClassPeriodApiController extends AbstractController
{
    public function __construct(
        private readonly SessionFetcher $sessionFetcher
    ) {
    }

    /**
     * Get student WithOut Class School to Period selected.
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function studentWithOut(ClassPeriodManager $manager): JsonResponse
    {
        $students = $manager->getListStudentWithout($this->sessionFetcher->getPeriodOnSession(), $this->sessionFetcher->getSchoolOnSession());

        return $this->json(['data' => ['students' => $students]]);
    }

    /**
     * @throws AppException
     * @throws ORMException
     */
    #[Route(
        path: '/update-student/{id}',
        name: 'app_api_class_period_update_student',
        options: ['expose' => true],
        methods: ['POST']
    )]
    public function updateStudent(Request $request, ClassPeriodManager $manager, ClassPeriod $classPeriod): JsonResponse
    {
        $students = $request->get('students');
        $success = $manager->treatListStudent($students, $this->sessionFetcher->getPeriodOnSession(), $classPeriod);

        return $this->json([
            'name' => $classPeriod->getClassSchool()->getName(),
            'count' => \count($classPeriod->getStudents()),
            'success' => $success,
        ]);
    }
}

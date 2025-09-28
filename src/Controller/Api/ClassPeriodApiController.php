<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\SessionFetcher;
use App\Manager\ClassPeriodManager;
use App\Repository\ClassPeriodRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassPeriodApiController extends AbstractController
{
    public function __construct(
        private readonly SessionFetcher $sessionFetcher,
        private readonly ClassPeriodRepository $classPeriodRepository,
        private readonly StudentRepository $studentRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get student WithOut Class School to Period selected.
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function studentWithOut(ClassPeriodManager $classPeriodManager): JsonResponse
    {
        $students = $classPeriodManager->getListStudentWithout($this->sessionFetcher->getPeriodOnSession(), $this->sessionFetcher->getSchoolOnSession());

        return $this->json(['data' => ['students' => $students]]);
    }

    /**
     * @throws AppException
     * @throws ORMException
     */
    #[Route(
        path: '/api/class-period/update-student/{id}',
        name: 'app_api_class_period_update_student',
        options: ['expose' => true],
        methods: ['POST']
    )]
    public function updateStudent(Request $request, ClassPeriodManager $classPeriodManager, ClassPeriod $classPeriod): JsonResponse
    {
        $students = $request->get('students');
        $success = $classPeriodManager->treatListStudent($students, $this->sessionFetcher->getPeriodOnSession(), $classPeriod);

        return $this->json([
            'name' => $classPeriod->getClassSchool()->getName(),
            'count' => \count($classPeriod->getStudents()),
            'success' => $success,
        ]);
    }

    #[Route(
        path: '/api/class-period/periods',
        name: 'app_api_class_periods',
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function getClassPeriods(Request $request): JsonResponse
    {
        try {
            $studentId = $request->query->getInt('studentId');
            $student = null;
            $currentClassPeriodId = null;

            if ($studentId > 0) {
                $student = $this->studentRepository->find($studentId);
                if (!$student instanceof Student) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Étudiant introuvable.',
                    ], Response::HTTP_NOT_FOUND);
                }
            }

            try {
                $period = $this->sessionFetcher->getPeriodOnSession();
                $school = $this->sessionFetcher->getSchoolOnSession();
            } catch (\Throwable $throwable) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Période ou école introuvable.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get current class period for student
            if ($student instanceof Student) {
                $classPeriodStudent = $student->getClassToPeriod($period);
                if ($classPeriodStudent instanceof ClassPeriodStudent) {
                    $currentClassPeriodId = $classPeriodStudent->getClassPeriod()?->getId();
                }
            }

            // Get available class periods
            $classPeriods = $this->classPeriodRepository->getClassPeriods($period, $school);
            $classPeriodsData = [];

            foreach ($classPeriods as $classPeriod) {
                $studentCount = 0;

                try {
                    $studentCount = $classPeriod->getStudents()->count();
                } catch (\Throwable $throwable) {
                    $this->logger->debug('Unable to count students for class period', [
                        'exception' => $throwable,
                        'classPeriod' => $classPeriod->getId(),
                    ]);
                }

                $classPeriodsData[] = [
                    'id' => $classPeriod->getId(),
                    'name' => $classPeriod->getClassSchool()->getName() ?? 'Classe inconnue',
                    'studentCount' => $studentCount,
                ];
            }

            return new JsonResponse([
                'success' => true,
                'classPeriods' => $classPeriodsData,
                'currentClassPeriodId' => $currentClassPeriodId,
            ]);
        } catch (\Throwable $throwable) {
            $this->logger->error('Error loading class periods', [
                'exception' => $throwable,
                'studentId' => $request->query->get('studentId'),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors du chargement des classes.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(
        path: '/api/class-period/change-student-class',
        name: 'app_api_change_student_class',
        options: ['expose' => true],
        methods: ['POST']
    )]
    public function changeStudentClass(Request $request, ClassPeriodManager $classPeriodManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($data['studentId'], $data['classPeriodId'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données manquantes.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $studentId = (int) $data['studentId'];
            $classPeriodId = (int) $data['classPeriodId'];

            $student = $this->studentRepository->find($studentId);
            $classPeriod = $this->classPeriodRepository->find($classPeriodId);

            if (!$student instanceof Student) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Étudiant introuvable.',
                ], Response::HTTP_NOT_FOUND);
            }

            if (!$classPeriod instanceof ClassPeriod) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Classe introuvable.',
                ], Response::HTTP_NOT_FOUND);
            }

            try {
                $period = $this->sessionFetcher->getPeriodOnSession();
            } catch (\Throwable $throwable) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Période courante introuvable.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $updated = $classPeriodManager->treatListStudent([$studentId], $period, $classPeriod);

            if ($updated) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Classe modifiée avec succès !',
                    'student' => [
                        'id' => $student->getId(),
                        'classPeriod' => [
                            'id' => $classPeriod->getId(),
                            'name' => $classPeriod->getClassSchool()->getName() ?? 'Classe inconnue',
                        ],
                    ],
                ]);
            }

            return new JsonResponse([
                'success' => false,
                'message' => 'Aucune modification effectuée.',
            ], Response::HTTP_BAD_REQUEST);
        } catch (\JsonException) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Format JSON invalide.',
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $throwable) {
            $this->logger->error('Error changing student class', [
                'exception' => $throwable,
                'data' => $data ?? null,
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la modification de la classe.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

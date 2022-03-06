<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Base\AbstractBaseController;
use App\Entity\ClassPeriod;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Manager\ClassPeriodManager;
use App\Model\ResponseModel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/class-period')]
class ClassPeriodApiController extends AbstractBaseController
{
    /**
     * Get student WithOut Class School to Period selected.
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function studentWithOut(ClassPeriodManager $manager): JsonResponse
    {
        $response = ResponseModel::responseDefault();
        $students = $manager->getListStudentWithout($this->getPeriod(), $this->getSchool());

        $response->setData(['students' => $students]);

        return ResponseModel::jsonResponse($response);
    }

    /**
     * @throws AppException
     */
    #[Route(
        path: '/update-student/{id}',
        name: 'app_api_class_period_update_student',
        options: ['expose' => true],
        methods: ['POST']
    )]
    public function updateStudent(Request $request, ClassPeriodManager $manager, ClassPeriod $classPeriod): JsonResponse
    {
        try {
            $students = $request->get('students');
            $success = $manager->treatListStudent($students, $this->getPeriod(), $classPeriod);

            return $this->json([
                'name' => $classPeriod->getClassSchool()->getName(),
                'count' => count($classPeriod->getStudents()),
                'success' => $success,
            ]);
        } catch (Exception $e) {
            $this->getLogger()->error(__METHOD__.' '.$e->getMessage(), $e->getTrace());

            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}

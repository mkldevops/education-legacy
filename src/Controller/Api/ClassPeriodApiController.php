<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Base\AbstractBaseController;
use App\Entity\ClassPeriod;
use App\Entity\Student;
use App\Exception\InvalidArgumentException;
use App\Manager\ClassPeriodManager;
use App\Model\ResponseModel;
use App\Services\ResponseRequest;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ClassPeriod controller.
 *
 * @Route("/api/class-period")
 */
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
     * addStudentAction.
     *
     * @Route(
     *     "/update-student/{id}",
     *     name="app_api_class_period_update_student",
     *     methods={"POST"},
     *     options={"expose"=true}
     *    )
     *
     * @return JsonResponse
     */
    public function updateStudent(Request $request, ClassPeriodManager $manager, ClassPeriod $classPeriod = null)
    {
        $name = $classPeriod->getClassSchool()->getName();

        $response = ResponseRequest::responseDefault([
            'name' => $name,
            'count' => null,
        ]);

        try {
            $students = $request->get('students');
            $response->success = $manager->treatListStudent($students, $this->getPeriod(), $classPeriod);

            if ($classPeriod instanceof ClassPeriod) {
                $response->count = count($classPeriod->getStudents());
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            $this->getLogger()->error(__METHOD__.' '.$e->getMessage(), $e->getTrace());
        }

        return new JsonResponse($response, empty($response->errors) ? 200 : 500);
    }

    /**
     * changeStudentAction.
     */
    public function changeStudentAction(Student $student): JsonResponse
    {
        $response = ResponseModel::responseDefault();

        return ResponseRequest::jsonResponse($response);
    }
}

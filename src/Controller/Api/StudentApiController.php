<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Student;
use App\Exception\AppException;
use App\Form\StudentType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/api/student', options: ['expose' => true])]
class StudentApiController extends AbstractBaseController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/create', name: 'app_api_student_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $response = $this->json([]);

        try {
            $student = new Student();
            $form = $this->createForm(StudentType::class, $student)
                ->handleRequest($request);

            $this->persistData($student, $form);

            $this->addFlash('success', 'The student has been added.');
            $response->setData(json_encode($student));
        } catch (AppException $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setData(['message' => $e->getMessage()])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * @throws AppException
     */
    private function persistData(Student $student, FormInterface $form): void
    {
        if (!$form->isSubmitted()) {
            throw new AppException('The form is not submitted ');
        }

        if (!$form->isValid()) {
            throw new AppException('The form is not valid '.$form->getErrors());
        }

        $em = $this->getDoctrine()->getManager();

        $student
            ->setSchool($this->getEntitySchool())
            ->setAuthor($this->security->getUser());

        $em->persist($student);
        $em->flush();
    }

    #[Route('/update/{id}', name: 'app_api_student_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Student $student): JsonResponse
    {
        $this->logger->info(__FUNCTION__);
        $response = $this->json([]);

        try {
            $form = $this->createForm(StudentType::class, $student)
                ->handleRequest($request);

            $this->persistData($student, $form);

            $this->addFlash('success', sprintf('The student %s has been updated.', $student->getNameComplete()));
            $response->setData(['student' => json_encode($student)]);
        } catch (AppException $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setData(['message' => $e->getMessage()])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}

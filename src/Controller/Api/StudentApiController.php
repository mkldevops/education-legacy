<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Student;
use App\Exception\AppException;
use App\Form\StudentType;
use App\Manager\SchoolManager;
use App\Model\StudentModel;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'app_api_', options: ['expose' => true])]
class StudentApiController extends AbstractController
{
    /**
     * Constructor.
     *
     * Stores injected dependencies used for persistence, logging, and obtaining the current school.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly SchoolManager $schoolManager,
    ) {}

    /**
     * Handle POST request to create a new Student from submitted form data and return its JSON representation.
     *
     * Validates and persists the new Student and adds a success flash message on success.
     *
     * @throws AppException If the form is not submitted or contains validation errors.
     * @return JsonResponse The created student serialized via StudentModel::fromStudent.
     */
    #[Route('/student/create', name: 'student_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student)
            ->handleRequest($request)
        ;

        $this->persistData($student, $form);

        $this->addFlash('success', 'The student has been added.');

        return $this->json(StudentModel::fromStudent($student));
    }

    /**
     * Update an existing Student from request data and return its JSON representation.
     *
     * Processes the submitted StudentType form bound to the provided Student entity,
     * validates and persists changes (via persistData), adds a success flash message,
     * and returns the updated student as JSON using StudentModel::fromStudent().
     *
     * @param Request $request HTTP request containing form data.
     * @param Student $student The Student entity to update (provided by route parameter).
     * @return JsonResponse JSON representation of the updated student.
     * @throws AppException If the form is not submitted or contains validation errors.
     */
    #[Route('/student/update/{id}', name: 'student_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Student $student): JsonResponse
    {
        $this->logger->info(__FUNCTION__);

        $form = $this->createForm(StudentType::class, $student)
            ->handleRequest($request)
        ;

        $this->persistData($student, $form);

        $this->addFlash('success', \sprintf('The student %s has been updated.', $student->getNameComplete()));

        return $this->json(StudentModel::fromStudent($student));
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

        $student
            ->setSchool($this->schoolManager->getEntitySchoolOnSession())
        ;

        $this->entityManager->persist($student);
        $this->entityManager->flush();
    }
}

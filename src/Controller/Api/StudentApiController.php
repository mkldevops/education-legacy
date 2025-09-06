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

class StudentApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly SchoolManager $schoolManager,
    ) {}

    /**
     * @throws AppException
     */
    #[Route('/api/student/create', name: 'app_api_student_create', methods: ['POST'], options: ['expose' => true])]
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
     * @throws AppException
     */
    #[Route('/api/student/update/{id}', name: 'app_api_student_update', methods: ['POST', 'PUT'], options: ['expose' => true])]
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

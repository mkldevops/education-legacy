<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route(path: '/course/save-appeal/{id}', name: 'app_course_save_appeal', methods: ['POST'])]
    public function saveAppeal(Request $request, Course $course, EntityManagerInterface $entityManager): JsonResponse
    {
        $response = (object) [
            'success' => false,
            'error' => [],
            'students' => [],
        ];

        $listStudents = $course->getStudents();
        $listStudentStatus = $request->get('listStudent');
        foreach ($listStudents as $listStudent) {
            $status = null;
            $studentId = $listStudent->getStudent()->getId();

            if (\array_key_exists($studentId, $listStudentStatus)) {
                $status = (int) $listStudentStatus[$studentId]['status'];
            } else {
                $name = $listStudent->getStudent()->getNameComplete();
                $response->error[] = \sprintf("%s n'est pas dans la liste", $name);
            }

            if (null !== $status && $status !== $listStudent->getStatus()) {
                $listStudent->setStatus($status);
                $entityManager->persist($listStudent);
            }
        }

        $entityManager->flush();

        return $this->json($response);
    }
}

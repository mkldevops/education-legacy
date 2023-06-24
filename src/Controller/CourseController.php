<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Course;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Manager\CourseManager;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/course')]
#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route(path: '/save-appeal/{id}', name: 'app_course_save_appeal', methods: ['POST'])]
    public function saveAppeal(Request $request, Course $course, EntityManagerInterface $entityManager): JsonResponse
    {
        $response = (object) [
            'success' => false,
            'error' => [],
            'students' => [],
        ];

        $listStudents = $course->getStudents();
        $listStudentStatus = $request->get('listStudent');
        foreach ($listStudents as $appealCourse) {
            $status = null;
            $studentId = $appealCourse->getStudent()->getId();

            if (\array_key_exists($studentId, $listStudentStatus)) {
                $status = (int) $listStudentStatus[$studentId]['status'];
            } else {
                $name = $appealCourse->getStudent()->getNameComplete();
                $response->error[] = sprintf("%s n'est pas dans la liste", $name);
            }

            if (null !== $status && $status !== $appealCourse->getStatus()) {
                $appealCourse->setStatus($status);
                $entityManager->persist($appealCourse);
            }
        }

        $entityManager->flush();

        return $this->json($response);
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/generate', name: 'app_course_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request, CourseManager $courseManager, PeriodManager $periodManager, SchoolManager $schoolManager): Response
    {
        set_time_limit(0);

        try {
            $courseManager->getGoogleCalendar()->getClient();
        } catch (AppException|\Exception $e) {
            $this->addFlash('warning', 'Your Token is not defined');

            return $this->redirectToRoute('app_google_auth');
        }

        $form = $this->createFormBuilder()
            ->add('generate', SubmitType::class)
            ->setMethod(Request::METHOD_POST)
            ->getForm()
            ->handleRequest($request)
        ;
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $courseManager->generate($periodManager->getPeriodsOnSession(), $schoolManager->getSchool());

            if (0 !== $result) {
                $this->addFlash('success', 'The course is successfully generated : '.$result);
            }

            return $this->redirectToRoute('app_admin_home');
        }

        return $this->render('course/generate.html.twig', [
            'form' => $form->createView(),
            'manager' => $courseManager,
        ]);
    }
}

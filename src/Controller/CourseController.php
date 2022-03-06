<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Course;
use App\Exception\AppException;
use App\Manager\CourseManager;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/course')]
#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractBaseController
{
    /**
     * @throws Exception
     */
    #[Route(path: '/save-appeal/{id}', name: 'app_course_save_appeal', methods: ['POST'])]
    public function saveAppeal(Request $request, Course $course): JsonResponse
    {
        $response = (object) [
            'success' => false,
            'error' => [],
            'students' => [],
        ];
        $manager = $this->getDoctrine()->getManager();
        $listStudents = $course->getStudents();
        $listStudentStatus = $request->get('listStudent');
        foreach ($listStudents as $appealCourse) {
            $status = null;
            $studentId = $appealCourse->getStudent()->getId();

            if (array_key_exists($studentId, $listStudentStatus)) {
                $status = (int) $listStudentStatus[$studentId]['status'];
            } else {
                $name = $appealCourse->getStudent()->getNameComplete();
                $response->error[] = sprintf('%s n\'est pas dans la liste', $name);
            }

            if (!is_null($status) && $status !== $appealCourse->getStatus()) {
                $appealCourse->setStatus($status);
                $appealCourse->setLastUpdate(new DateTime());

                $manager->persist($appealCourse);
            }
        }
        $manager->flush();

        return $this->json($response);
    }

    #[Route(path: '/generate', name: 'app_course_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request, CourseManager $courseManager): Response
    {
        set_time_limit(0);

        try {
            $courseManager->getGoogleCalendar()->getClient();
        } catch (AppException $e) {
            $this->addFlash('warning', 'Your Token is not defined');

            return $this->redirectToRoute('app_google_auth');
        }

        $form = $this->createFormBuilder()
            ->add('generate', SubmitType::class)
            ->setMethod(Request::METHOD_POST)
            ->getForm()
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $courseManager->generate($this->getPeriod(), $this->getSchool());

            if (0 !== $result) {
                $this->addFlash('success', 'The course is successfully generated : '.$result);
            } else {
                $logs = $courseManager->getLogger()->getLogs();
                $this->addFlash('danger', 'An error occurred during the process <br />'.print_r($logs, true));
            }

            return $this->redirectToRoute('app_course_index');
        }

        return $this->render('course/generate.html.twig', [
            'form' => $form->createView(),
            'manager' => $courseManager,
        ]);
    }
}

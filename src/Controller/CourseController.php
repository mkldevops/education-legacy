<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\AppealCourse;
use App\Entity\Course;
use App\Exception\InvalidArgumentException;
use App\Form\CourseType;
use App\Manager\CourseManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Course controller.
 *
 * @IsGranted("ROLE_TEACHER")
 */
#[\Symfony\Component\Routing\Annotation\Route(path: '/course')]
class CourseController extends AbstractBaseController
{
    /**
     *
     *
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '', name: 'app_course_index', methods: ['GET'])]
    public function index(EntityManagerInterface $manager, int $page = 1, string $search = '') : \Symfony\Component\HttpFoundation\Response
    {
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        // Get the total entries.
        $count = $manager
            ->getRepository(Course::class)
            ->getListQueryBuilder($search, $this->getSchool(), $this->getPeriod())
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
        // Define the number of pages.
        $pages = ceil($count / 20);
        // Get the entries of current page.
        /* @var $courseList Course[] */
        $courseList = $manager
            ->getRepository(Course::class)
            ->getListQueryBuilder($search, $this->getSchool(), $this->getPeriod())
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        return $this->render('Course/index.html.twig', [
            'courseList' => $courseList,
            'pages' => $pages,
            'page' => $page,
            'count' => $count,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }
    /**
     * Creates a form to search Course entities.
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_course_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }
    /**
     * Creates a new Course entity.
     *
     *
     *
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/create', name: 'app_course_create', methods: ['POST'])]
    public function create(Request $request) : \Symfony\Component\HttpFoundation\Response
    {
        $course = new Course();
        $form = $this->createCreateForm($course);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            foreach ($course->getTeachers() as $teacher) {
                $teacher->addCourse($course);
                $manager->persist($teacher);
            }

            $course->setAuthor($this->getUser());
            $manager->persist($course);
            $manager->flush();

            foreach ($course->getClassPeriod()->getStudents() as $classPeriodStudent) {
                $appealCourse = new AppealCourse();
                $appealCourse->setStudent($classPeriodStudent->getStudent());
                $appealCourse->setCourse($course);

                $manager->persist($appealCourse);
            }

            $manager->flush();

            $this->addFlash('success', 'The Course has been created.');

            return $this->redirect($this->generateUrl('app_course_show', ['id' => $course->getId()]));
        }
        return $this->render('Course/create.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Creates a form to create a Course entity.
     *
     * @param Course $course The entity
     */
    private function createCreateForm(Course $course): FormInterface
    {
        $form = $this->createForm(CourseType::class, $course, [
            'action' => $this->generateUrl('app_course_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }
    /**
     * Displays a form to create a new Course entity.
     *
     *
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/new', name: 'app_course_new', methods: ['GET'])]
    public function new() : \Symfony\Component\HttpFoundation\Response
    {
        $course = new Course();
        $form = $this->createCreateForm($course);
        return $this->render('Course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Finds and displays a Course entity.
     *
     *
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/show/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course = null) : \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('Course/show.html.twig', [
            'course' => $course,
            'listStatus' => CourseManager::getListStatus(),
        ]);
    }
    /**
     * Displays a form to edit an existing Course entity.
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/edit/{id}', name: 'app_course_edit', methods: ['GET'])]
    public function edit(Course $course) : \Symfony\Component\HttpFoundation\Response
    {
        $editForm = $this->createEditForm($course);
        return $this->render('Course/edit.html.twig', [
            'course' => $course,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Creates a form to edit a Course entity.
     *
     * @param Course $course The entity
     */
    private function createEditForm(Course $course): FormInterface
    {
        $form = $this->createForm(CourseType::class, $course, [
            'action' => $this->generateUrl('app_course_update', ['id' => $course->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }
    /**
     * Displays a form to edit an existing Course entity.
     *
     *
     *
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/save-appeal/{id}', name: 'app_course_save_appeal', methods: ['POST'])]
    public function saveAppeal(Request $request, Course $course) : \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = (object)[
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
                $status = (int)$listStudentStatus[$studentId]['status'];
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
        return new JsonResponse($response);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: '/generate', name: 'app_course_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request, CourseManager $courseManager) : Response
    {
        set_time_limit(0);
        try {
            $courseManager->getGoogleCalendar()
                ->getClient();
        } catch (Exception $e) {
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
                $this->addFlash('success', 'The course is successfully generated : ' . $result);
            } else {
                $logs = $courseManager->getLogger()->getLogs();
                $this->addFlash('danger', 'An error occurred during the process <br />' . print_r($logs, true));
            }

            return $this->redirectToRoute('app_course_index');
        }
        return $this->render('course/generate.html.twig', [
            'form' => $form->createView(),
            'manager' => $courseManager,
        ]);
    }
}

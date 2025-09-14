<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Period;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Exception\SchoolException;
use App\Fetcher\SessionFetcher;
use App\Form\ClassPeriodType;
use App\Manager\ClassPeriodManager;
use App\Manager\CourseManager;
use App\Manager\Interfaces\ClassPeriodManagerInterface;
use App\Repository\AppealCourseRepository;
use App\Repository\ClassPeriodRepository;
use App\Repository\ClassSchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
class ClassPeriodController extends AbstractController
{
    /**
     * @var int
     */
    private const NB_LINES_MIN = 30;

    /**
     * @var int
     */
    private const NB_DATES = 17;

    public function __construct(
        private readonly ClassSchoolRepository $classSchoolRepository,
        private readonly ClassPeriodRepository $classPeriodRepository,
    ) {}

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[Route(path: '/class-period/current', name: 'app_class_period_current', methods: ['GET'])]
    public function current(SessionFetcher $sessionFetcher): Response
    {
        $this->checkClasses();

        return $this->render('class_period/index.html.twig', [
            'classperiodList' => $this->classPeriodRepository->getListOfCurrentPeriod(
                period: $sessionFetcher->getPeriodOnSession(),
                school: $sessionFetcher->getSchoolOnSession()
            ),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     * @throws SchoolException
     * @throws NoResultException
     */
    #[Route(path: '/class-period', name: 'app_class_period_index', methods: ['GET'])]
    public function index(SessionFetcher $sessionFetcher, int $page = 1, string $search = ''): Response
    {
        $this->checkClasses();
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        $count = $this->classPeriodRepository->countClassesPeriod($sessionFetcher->getSchoolOnSession(), $search);
        $pages = ceil($count / 20);
        // Get the entries of current page.
        $classperiodList = $this->classPeriodRepository->getList($sessionFetcher->getSchoolOnSession(), $page, $search);

        return $this->render('class_period/index.html.twig', [
            'classperiodList' => $classperiodList,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }

    /**
     * @throws \LogicException
     */
    #[Route(path: '/class-period/create', name: 'app_class_period_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $classPeriod = new ClassPeriod();
        $form = $this->createCreateForm($classPeriod);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entityManager->persist($classPeriod);
            $entityManager->flush();

            $this->addFlash('success', 'The ClassPeriod has been created.');

            return $this->redirectToRoute('app_class_period_show', ['id' => $classPeriod->getId()]);
        }

        return $this->render('class_period/new.html.twig', [
            'classPeriod' => $classPeriod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to create a new ClassPeriod entity.
     */
    #[Route(path: '/class-period/new', name: 'app_class_period_new', methods: ['GET'])]
    public function new(): Response
    {
        $this->checkClasses();
        $classperiod = new ClassPeriod();
        $form = $this->createCreateForm($classperiod);

        return $this->render('class_period/new.html.twig', [
            'classperiod' => $classperiod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws \Exception
     */
    #[Route(path: '/class-period/show/{id}', name: 'app_class_period_show', options: ['expose' => true], methods: ['GET'])]
    public function show(ClassPeriod $classPeriod, AppealCourseRepository $appealCourseRepository): Response
    {
        $listStatus = CourseManager::getListStatus();
        $appeals = $appealCourseRepository->getAppealToClassPeriod($classPeriod, $listStatus);

        return $this->render('class_period/show.html.twig', [
            'classperiod' => $classPeriod,
            'appeals' => $appeals,
            'listStatus' => $listStatus,
        ]);
    }

    #[Route(path: '/class-period/edit/{id}', name: 'app_class_period_edit', methods: ['GET'])]
    public function edit(ClassPeriod $classPeriod): Response
    {
        $editForm = $this->createEditForm($classPeriod);

        return $this->render('class_period/edit.html.twig', [
            'classperiod' => $classPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/class-period/update/{id}', name: 'app_class_period_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, ClassPeriod $classPeriod, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($classPeriod);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The ClassPeriod has been updated.');

            return $this->redirectToRoute('app_class_period_show', ['id' => $classPeriod->getId()]);
        }

        return $this->render('class_period/edit.html.twig', [
            'classperiod' => $classPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/class-period/delete/{id}', name: 'app_class_period_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, ClassPeriod $classPeriod): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($classPeriod->getId())
            ->handleRequest($request)
        ;
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $this->classPeriodRepository->remove($classPeriod);
            $this->addFlash('success', 'The ClassPeriod has been deleted.');

            return $this->redirectToRoute('app_class_period_current');
        }

        return $this->render('class_period/delete.html.twig', [
            'classperiod' => $classPeriod,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route('/class-period/search', name: 'app_class_period_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirectToRoute('app_class_period_index', [
            'page' => 1,
            'search' => urlencode((string) $all['form']['q']),
        ]);
    }

    #[Route(path: '/class-period/add/{period}/{classSchool}', name: 'app_class_period_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function add(ClassSchool $classSchool, Period $period, EntityManagerInterface $entityManager): RedirectResponse
    {
        $classPeriod = $this->classPeriodRepository->findOneBy(['classSchool' => $classSchool, 'period' => $period]);
        if (null === $classPeriod) {
            $classPeriod = new ClassPeriod();
            $classPeriod->setClassSchool($classSchool)
                ->setPeriod($period)
            ;

            $entityManager->persist($classPeriod);
            $entityManager->flush();

            $this->addFlash('info', 'La Classe '.$classSchool->getName().' a bien ajouté à la periode '.$period->getName());
        } else {
            $this->addFlash('error', 'La Classe '.$classSchool->getName()." n'a pas été ajouté à la periode ".$period->getName().', celle-ci esxiste ');
        }

        return $this->redirectToRoute('app_class_period_show', ['id' => $classPeriod->getId()]);
    }

    #[Route(path: '/class-period/show-student/{id}', name: 'app_class_period_show_student', methods: ['GET'])]
    public function showStudent(ClassPeriod $classPeriod, ClassPeriodManagerInterface $classPeriodManager): Response
    {
        return $this->render('class_period/showStudent.html.twig', [
            'classperiod' => $classPeriod,
            'students' => $classPeriodManager->getStudentsInClassPeriod($classPeriod),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: '/class-period/print-list-student/{id}', name: 'app_class_period_print_list_student', methods: ['GET'])]
    public function printListStudent(ClassPeriod $classPeriod, ClassPeriodManager $classPeriodManager): Response
    {
        $packageStudents = $classPeriodManager->getPackageStudent($classPeriod);
        $students = $classPeriodManager->getStudentsInClassPeriod($classPeriod);
        $lines = \count($students);
        if ($lines < self::NB_LINES_MIN) {
            $lines = self::NB_LINES_MIN;
        }

        return $this->render('class_period/print_list_student.html.twig', [
            'classperiod' => $classPeriod,
            'students' => $students,
            'packageStudents' => $packageStudents,
            'lines' => $lines,
        ]);
    }

    #[Route(
        '/class-period/print-appeal-student/{id}/{page}/{from}',
        name: 'app_class_period_print_appeal_student',
        methods: ['GET']
    )]
    public function printAppealStudent(
        ClassPeriod $classPeriod,
        ClassPeriodManager $classPeriodManager,
        int $page = 1,
        ?\DateTimeInterface $from = null
    ): Response {
        $from ??= new \DateTime();
        $students = $classPeriodManager->getStudentsInClassPeriod($classPeriod);
        $courses = $classPeriodManager->getCourses($classPeriod, $page, self::NB_DATES, $from);
        $nbCourses = $classPeriodManager->getNbCourses($classPeriod, $from) ?? self::NB_DATES;

        // Define the number of pages.
        $pages = ceil($nbCourses / self::NB_DATES);

        $lines = \count($students);
        if ($lines < self::NB_LINES_MIN) {
            $lines = self::NB_LINES_MIN;
        }

        return $this->render('class_period/print_appeal_student.html.twig', [
            'classperiod' => $classPeriod,
            'students' => $students,
            'lines' => $lines,
            'page' => $page,
            'pages' => $pages,
            'courses' => $courses,
            'from' => $from,
        ]);
    }

    /**
     * @throws \LogicException
     */
    #[Route('/class-period/delete-student/{id}/{student}', name: 'app_class_period_delete_student', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteStudent(
        EntityManagerInterface $entityManager,
        ClassPeriod $classPeriod,
        Student $student
    ): RedirectResponse {
        $list = $this->classPeriodRepository->getStudentToClassPeriod($classPeriod, $student);

        $classPeriodStudent = current($list);

        if (!empty($classPeriodStudent)) {
            $entityManager->remove($classPeriodStudent);
            $entityManager->flush();

            $this->addFlash('info', \sprintf(
                "L'élève %s à été supprimé de la classe %s",
                $student->getNameComplete(),
                $classPeriod->getName()
            ));
        } else {
            $this->addFlash('danger', \sprintf(
                'L\'élève "%s" n\'est pas présent dans la classe "%s"',
                $student->getNameComplete(),
                $classPeriod->getName()
            ));
        }

        return $this->redirectToRoute('app_class_period_show_student', ['id' => $classPeriod->getId()]);
    }

    #[Route('/class-period/change-student/{id}/{student}', name: 'app_class_period_change_student', methods: ['GET'])]
    public function changeStudent(ClassPeriod $classPeriod, Student $student): RedirectResponse
    {
        return $this->redirectToRoute('app_class_period_show_student', ['id' => $classPeriod->getId()]);
    }

    private function checkClasses(): void
    {
        $nbClasses = $this->classSchoolRepository->count(['enable' => true]);

        if (empty($nbClasses)) {
            $url = $this->generateUrl('app_admin_dashboard_index', ['entity' => 'ClassSchool']);
            $this->addFlash(
                'danger',
                \sprintf('Vous n\'avez pas de classes <a href="%s">Ajouter une class</a>', $url)
            );
        }
    }

    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_class_period_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextareaType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm()
        ;
    }

    /**
     * Creates a form to create a ClassPeriod entity.
     *
     * @param ClassPeriod $classPeriod The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(ClassPeriod $classPeriod): FormInterface
    {
        $form = $this->createForm(ClassPeriodType::class, $classPeriod, [
            'action' => $this->generateUrl('app_class_period_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    private function createEditForm(ClassPeriod $classPeriod): FormInterface
    {
        return $this->createForm(ClassPeriodType::class, $classPeriod, [
            'action' => $this->generateUrl('app_class_period_update', ['id' => $classPeriod->getId()]),
            'method' => Request::METHOD_PUT,
        ])->add('submit', SubmitType::class, ['label' => 'Update']);
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_class_period_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}

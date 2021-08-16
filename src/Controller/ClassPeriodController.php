<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\AppealCourse;
use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Period;
use App\Entity\Student;
use App\Form\ClassPeriodType;
use App\Manager\ClassPeriodManager;
use App\Manager\CourseManager;
use App\Repository\ClassPeriodRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use InvalidArgumentException;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/class-period')]
#[IsGranted('ROLE_TEACHER')]
class ClassPeriodController extends AbstractBaseController
{
    private const NB_LINES_MIN = 30;
    private const NB_DATES = 17;

    /**
     * Lists all ClassPeriod entities.
     *
     *
     * @throws \App\Exception\InvalidArgumentException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/current', name: 'app_class_period_current', methods: ['GET'])]
    public function current(ClassPeriodManager $manager) : Response
    {
        $this->checkClasses();
        $classperiodList = $manager->getListOfCurrentPeriod($this->getPeriod(), $this->getSchool());
        return $this->render('class_period/index.html.twig', [
            'classperiodList' => $classperiodList,
        ]);
    }

    private function checkClasses(): void
    {
        $nbClasses = $this->getDoctrine()
            ->getRepository(ClassSchool::class)
            ?->count(['enable' => true]);

        if (empty($nbClasses)) {
            $url = $this->generateUrl('app_admin_dashboard_index', ['entity' => 'ClassSchool']);
            $this->addFlash(
                'danger',
                sprintf('Vous n\'avez pas de classes <a href="%s">Ajouter une class</a>', $url)
            );
        }
    }

    /**
     * Lists all ClassPeriod entities.
     *
     *
     * @throws NonUniqueResultException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '', name: 'app_class_period_index', methods: ['GET'])]
    public function index(ClassPeriodManager $manager, int $page = 1, string $search = '') : Response
    {
        $this->checkClasses();
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        $count = $manager->count($this->getSchool(), $search);
        $pages = ceil($count / 20);
        // Get the entries of current page.
        $classperiodList = $manager->getList($this->getSchool(), $page, $search);
        return $this->render('class_period/index.html.twig', [
            'classperiodList' => $classperiodList,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
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
            ->getForm();
    }

    /**
     * Creates a new ClassPeriod entity.
     *
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/create', name: 'app_class_period_create', methods: ['POST'])]
    public function create(Request $request) : \Symfony\Component\HttpFoundation\Response
    {
        $classperiod = new ClassPeriod();
        $form = $this->createCreateForm($classperiod);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $classperiod->setAuthor($this->getUser());
            $manager->persist($classperiod);
            $manager->flush();

            $this->addFlash('success', 'The ClassPeriod has been created.');

            return $this->redirect($this->generateUrl('app_class_period_show', ['id' => $classperiod->getId()]));
        }
        return $this->render('Clas1_Period/create.html.twig', [
            'classperiod' => $classperiod,
            'form' => $form->createView(),
        ]);
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

    /**
     * Displays a form to create a new ClassPeriod entity.
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/new', name: 'app_class_period_new', methods: ['GET'])]
    public function new() : \Symfony\Component\HttpFoundation\Response
    {
        $this->checkClasses();
        $classperiod = new ClassPeriod();
        $form = $this->createCreateForm($classperiod);
        return $this->render('Clas1_Period/new.html.twig', [
            'classperiod' => $classperiod,
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/show/{id}', name: 'app_class_period_show', methods: ['GET'], options: ['expose' => true])]
    public function show(ClassPeriod $classperiod) : Response
    {
        $manager = $this->getDoctrine()->getManager();
        $listStatus = CourseManager::getListStatus();
        $appeals = $manager->getRepository(AppealCourse::class)
            ->getAppealToClassPeriod($classperiod, $listStatus);
        return $this->render('class_period/show.html.twig', [
            'classperiod' => $classperiod,
            'appeals' => $appeals,
            'listStatus' => $listStatus,
        ]);
    }

    #[\Symfony\Component\Routing\Annotation\Route(path: '/edit/{id}', name: 'app_class_period_edit', methods: ['GET'])]
    public function edit(ClassPeriod $classPeriod) : Response
    {
        $editForm = $this->createEditForm($classPeriod);
        return $this->render('class_period/edit.html.twig', [
            'classperiod' => $classPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    private function createEditForm(ClassPeriod $classPeriod): FormInterface
    {
        return $this->createForm(ClassPeriodType::class, $classPeriod, [
            'action' => $this->generateUrl('app_class_period_update', ['id' => $classPeriod->getId()]),
            'method' => Request::METHOD_PUT,
        ])->add('submit', SubmitType::class, ['label' => 'Update']);
    }

    #[\Symfony\Component\Routing\Annotation\Route(path: '/update/{id}', name: 'app_class_period_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, ClassPeriod $classPeriod) : Response
    {
        $editForm = $this->createEditForm($classPeriod);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The ClassPeriod has been updated.');

            return $this->redirect($this->generateUrl('app_class_period_show', ['id' => $classPeriod->getId()]));
        }
        return $this->render('class_period/edit.html.twig', [
            'classperiod' => $classPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[\Symfony\Component\Routing\Annotation\Route(path: '/delete/{id}', name: 'app_class_period_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, ClassPeriodRepository $repository, ClassPeriod $classPeriod) : \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $deleteForm = $this->createDeleteForm($classPeriod->getId())
            ->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $repository->remove($classPeriod);
            $this->addFlash('success', 'The ClassPeriod has been deleted.');

            return $this->redirect($this->generateUrl('app_class_period_current'));
        }
        return $this->render('class_period/delete.html.twig', [
            'classperiod' => $classPeriod,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_class_period_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    #[Route('/search', name: 'app_class_period_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_class_period_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/add/{period}/{classSchool}', name: 'app_class_period_add', methods: ['GET', 'POST'])]
    public function add(ClassSchool $classSchool, Period $period) : RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $classPeriod = $em->getRepository(ClassPeriod::class)
            ->findBy(['classSchool' => $classSchool, 'period' => $period]);
        if (empty($classPeriod)) {
            $classPeriod = new ClassPeriod();
            $classPeriod->setAuthor($this->getUser())
                ->setClassSchool($classSchool)
                ->setPeriod($period);

            $em->persist($classPeriod);
            $em->flush();

            $this->addFlash('info', 'La Classe ' . $classSchool->getName() . ' a bien ajouté à la periode ' . $period->getName());
        } else {
            $this->addFlash('error', 'La Classe ' . $classSchool->getName() . ' n\'a pas été ajouté à la periode ' . $period->getName() . ', celle-ci esxiste ');
        }
        return $this->redirect($this->generateUrl('app_class_period_show', ['id' => $classPeriod->getId()]));
    }

    #[\Symfony\Component\Routing\Annotation\Route(path: '/show-student/{id}', name: 'app_class_period_show_student', methods: ['GET'])]
    public function showStudent(ClassPeriod $classPeriod, ClassPeriodManager $manager) : Response
    {
        return $this->render('class_period/showStudent.html.twig', [
            'classperiod' => $classPeriod,
            'students' => $manager->getStudentsInClassPeriod($classPeriod),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/print-list-student/{id}', name: 'app_class_period_print_list_student', methods: ['GET'])]
    public function printListStudent(ClassPeriod $classPeriod, ClassPeriodManager $manager) : Response
    {
        $packageStudents = $manager->getPackageStudent($classPeriod);
        $students = $manager->getStudentsInClassPeriod($classPeriod);
        $lines = count($students);
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

    /**
     * @ParamConverter("from", options={"format": "Y-m-d"})
     *
     * @param \DateTime|\DateTimeImmutable $from
     * @throws Exception
     *
     */
    #[Route(
        '/print-appeal-student/{id}/{page}/{from}',
        name: 'app_class_period_print_appeal_student',
        methods: ['GET']
    )]
    public function printAppealStudent(
        Request            $request,
        ClassPeriod        $classPeriod,
        ClassPeriodManager $manager,
        int                $page = 1,
        \DateTimeInterface $from = null
    ): Response {
        $from = $from ?? new DateTime();
        $students = $manager->getStudentsInClassPeriod($classPeriod);
        $courses = $manager->getCourses($classPeriod, $page, self::NB_DATES, $from);
        $nbCourses = $manager->getNbCourses($classPeriod, $from) ?? self::NB_DATES;

        // Define the number of pages.
        $pages = ceil($nbCourses / self::NB_DATES);

        $lines = count($students);
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
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    #[Route('/delete-student/{id}/{student}', name: 'app_class_period_delete_student', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteStudent(ClassPeriodRepository $repository, ClassPeriod $classPeriod, Student $student): RedirectResponse
    {
        $list = $repository->getStudentToClassPeriod($classPeriod, $student);

        $classPeriodStudent = current($list);

        if (!empty($classPeriodStudent)) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($classPeriodStudent);
            $manager->flush();

            $this->addFlash('info', sprintf(
                'L\'élève %s à été supprimé de la classe %s',
                $student->getNameComplete(),
                $classPeriod->getName()
            ));
        } else {
            $this->addFlash('danger', sprintf(
                'L\'élève "%s" n\'est pas présent dans la classe "%s"',
                $student->getNameComplete(),
                $classPeriod->getName()
            ));
        }

        return $this->redirect($this->generateUrl('app_class_period_show_student', ['id' => $classPeriod->getId()]));
    }

    #[Route('/change-student/{id}/{student}', name: 'app_class_period_change_student', methods: ['GET'])]
    public function changeStudent(ClassPeriod $classPeriod, Student $student): RedirectResponse
    {
        return $this->redirect($this->generateUrl('app_class_period_show_student', ['id' => $classPeriod->getId()]));
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\ClassSchoolType;
use App\Manager\ClassSchoolManager;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use App\Repository\ClassSchoolRepository;
use App\Repository\PeriodRepository;
use App\Repository\StudentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/class-school')]
class ClassSchoolController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[Route(path: '', name: 'app_class_school_index', methods: ['GET'])]
    public function index(ClassSchoolRepository $repository, SchoolManager $schoolManager, PeriodManager $periodManager): Response
    {
        $classSchools = $repository->findBy(['school' => $schoolManager->getSchool()], ['enable' => 'DESC']);

        return $this->render('class_school/index.html.twig', [
            'class_schools' => $classSchools,
            'period' => $periodManager->getPeriodsOnSession(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/create', name: 'app_class_school_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SchoolManager $schoolManager): Response
    {
        $classSchool = new ClassSchool();
        $form = $this->createCreateForm($classSchool);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $classSchool->setAuthor($this->getUser())
                ->setSchool($schoolManager->getEntitySchool())
            ;

            $entityManager->persist($classSchool);
            $entityManager->flush();

            $this->addFlash('success', 'The classSchool has been created.');

            return $this->redirect($this->generateUrl('app_class_school_show', ['id' => $classSchool->getId()]));
        }

        return $this->render('class_school/new.html.twig', [
            'classschool' => $classSchool,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to create a new classSchool entity.
     */
    #[Route(path: '/new', name: 'app_class_school_new', methods: ['GET'])]
    public function new(): Response
    {
        $classSchool = new ClassSchool();
        $form = $this->createCreateForm($classSchool);

        return $this->render('class_school/new.html.twig', [
            'classschool' => $classSchool,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/show/{id}', name: 'app_class_school_show', methods: ['GET'])]
    public function show(ClassSchool $classSchool, PeriodManager $periodManager, PeriodRepository $periodRepository): Response
    {
        $periods = $periodRepository->getLastPeriods($periodManager->getPeriodsOnSession());

        return $this->render('class_school/show.html.twig', [
            'classschool' => $classSchool,
            'periods' => $periods,
        ]);
    }

    /**
     * Displays a form to edit an existing classSchool entity.
     */
    #[Route(path: '/edit/{id}', name: 'app_class_school_edit', methods: ['GET'])]
    public function edit(ClassSchool $classSchool): Response
    {
        $editForm = $this->createEditForm($classSchool);

        return $this->render('class_school/edit.html.twig', [
            'classschool' => $classSchool,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/update/{id}', name: 'app_class_school_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, ClassSchool $classSchool, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($classSchool);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The classSchool has been updated.');

            return $this->redirect($this->generateUrl('app_class_school_show', ['id' => $classSchool->getId()]));
        }

        return $this->render('class_school/edit.html.twig', [
            'classschool' => $classSchool,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a classSchool entity.
     */
    #[Route(path: '/delete/{id}', name: 'app_class_school_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, ClassSchool $classSchool, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($classSchool->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($classSchool);
            $entityManager->flush();

            $this->addFlash('success', 'The classSchool has been deleted.');

            return $this->redirect($this->generateUrl('app_class_school_index'));
        }

        return $this->render('class_school/delete.html.twig', [
            'classschool' => $classSchool,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_class_school_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_class_school_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/view-class-period/{id}', name: 'app_class_school_view_class_period', methods: ['POST', 'GET'])]
    public function viewClassPeriod(Request $request, ClassPeriod $classPeriod, ClassSchoolManager $schoolManager): RedirectResponse
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var int[] $students */
            $students = $request->request->get('students', []);
            $result = $schoolManager->addStudentToClass($students, $classPeriod);

            if ($result) {
                $this->addFlash(
                    'info',
                    'Les élèves ont été ajouté à la Classe '.$classPeriod->getclassSchool()->getName().' pour la periode '.$classPeriod->getPeriod()->getName()
                );

                return $this->redirect($this->generateUrl('app_class_period_show', ['id' => $classPeriod->getId()]));
            }

            $this->addFlash('success', 'The student has not added');
        }

        return $this->redirect($this->generateUrl('app_class_period_show_student', [
            'id' => $classPeriod->getId(),
        ]));
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Route(path: '/without-student', name: 'app_class_school_without_student', options: ['expose' => true], methods: ['POST'])]
    public function withOutStudent(StudentRepository $studentRepository, SchoolManager $schoolManager, PeriodManager $periodManager): Response
    {
        $students = $studentRepository->getListStudentsWithoutClassPeriod($periodManager->getPeriodsOnSession(), $schoolManager->getSchool());
        $aListStudent = [];
        foreach ($students as $student) {
            /** @var DateTime $birthday */
            $birthday = $student['birthday'];
            $age = $birthday->diff(new DateTime())->y;

            if (!\array_key_exists($age, $aListStudent)) {
                $aListStudent[$age] = [];
            }

            $aListStudent[$age][] = $student;
        }
        ksort($aListStudent);

        return $this->render('class_period/students.html.twig', [
            'listStudents' => $aListStudent,
            'numberStudents' => \count($students),
        ]);
    }

    /**
     * Creates a form to create a classSchool entity.
     *
     * @param ClassSchool $classSchool The entity
     */
    private function createCreateForm(ClassSchool $classSchool): FormInterface
    {
        $form = $this->createForm(ClassSchoolType::class, $classSchool, [
            'action' => $this->generateUrl('app_class_school_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    private function createEditForm(ClassSchool $classSchool): FormInterface
    {
        $form = $this->createForm(ClassSchoolType::class, $classSchool, [
            'action' => $this->generateUrl('app_class_school_update', ['id' => $classSchool->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_class_school_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}

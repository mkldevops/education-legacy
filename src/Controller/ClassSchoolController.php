<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\ClassPeriod;
use App\Entity\ClassSchool;
use App\Entity\Period;
use App\Entity\Student;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\ClassSchoolType;
use App\Manager\ClassSchoolManager;
use DateTime;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Hamada Sidi Fahari <h.fahari@gmail.com>
 *
 * @Route("/class-school")
 */
class ClassSchoolController extends BaseController
{
    /**
     * @Route("", name="app_class_school_index", methods={"GET"})
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function index()
    {
        /** @var ClassSchool[] $classSchools */
        $classSchools = $this->getDoctrine()
            ->getManager()
            ->getRepository(ClassSchool::class)
            ->findBy(['school' => $this->getSchool()], ['enable' => 'DESC']);

        return $this->render('class_school/index.html.twig', [
            'class_schools' => $classSchools,
            'period' => $this->getPeriod(),
        ]);
    }

    /**
     * @Route("/create", name="app_class_school_create", methods={"POST"})
     * @throws AppException
     */
    public function create(Request $request): Response
    {
        $classSchool = new ClassSchool();
        $form        = $this->createCreateForm($classSchool);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $classSchool->setAuthor($this->getUser())
                ->setSchool($this->getEntitySchool());

            $em->persist($classSchool);
            $em->flush();

            $this->addFlash('success', 'The classSchool has been created.');

            return $this->redirect($this->generateUrl('app_class_school_show', ['id' => $classSchool->getId()]));
        }

        return $this->render('class_school/new.html.twig', [
            'classschool' => $classSchool,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a classSchool entity.
     *
     * @param ClassSchool $classSchool The entity
     *
     * @return FormInterface
     */
    private function createCreateForm(ClassSchool $classSchool)
    {
        $form = $this->createForm(ClassSchoolType::class, $classSchool, [
            'action' => $this->generateUrl('app_class_school_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new classSchool entity.
     *
     * @Route("/new", name="app_class_school_new", methods={"GET"})
     */
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
     * Show class School.
     *
     * @Route("/show/{id}", name="app_class_school_show", methods={"GET"})
     *
     * @throws InvalidArgumentException
     */
    public function show(ClassSchool $classSchool): Response
    {
        $periods = $this->getDoctrine()
            ->getManager()
            ->getRepository(Period::class)
            ->getLastPeriods($this->getPeriod());

        return $this->render('class_school/show.html.twig', [
            'classschool' => $classSchool,
            'periods' => $periods,
        ]);
    }

    /**
     * Displays a form to edit an existing classSchool entity.
     *
     * @Route("/edit/{id}", name="app_class_school_edit", methods={"GET"})
     *
     * @return Response
     */
    public function edit(ClassSchool $classSchool)
    {
        $editForm = $this->createEditForm($classSchool);

        return $this->render('class_school/edit.html.twig', [
            'classschool' => $classSchool,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a classSchool entity.
     *
     * @param ClassSchool $classSchool The entity
     *
     * @return FormInterface
     */
    private function createEditForm(ClassSchool $classSchool)
    {
        $form = $this->createForm(ClassSchoolType::class, $classSchool, [
            'action' => $this->generateUrl('app_class_school_update', ['id' => $classSchool->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing classSchool entity.
     *
     * @Route("/update/{id}", name="app_class_school_update", methods={"POST", "PUT"})
     *
     * @param ClassSchool|null $classSchool
     *
     * @return RedirectResponse|Response
     */
    public function update(Request $request, ClassSchool $classSchool): Response
    {
        $editForm = $this->createEditForm($classSchool);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

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
     *
     * @Route("/delete/{id}", name="app_class_school_delete", methods={"GET", "DELETE"})
     *
     * @return RedirectResponse|Response
     */
    public function delete(Request $request, ClassSchool $classSchool)
    {
        $deleteForm = $this->createDeleteForm($classSchool->getId());
        $deleteForm->handleRequest($request);

        $manager = $this->getDoctrine()->getManager();

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $manager->remove($classSchool);
            $manager->flush();

            $this->addFlash('success', 'The classSchool has been deleted.');

            return $this->redirect($this->generateUrl('app_class_school_index'));
        }

        return $this->render('class_school/delete.html.twig', [
            'classschool' => $classSchool,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a classSchool entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return FormInterface
     */
    private function createDeleteForm(int $id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_class_school_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @Route("/search", name="app_class_school_search", methods={"GET"})
     *
     * @return RedirectResponse
     */
    public function search(Request $request)
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_class_school_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * @Route("/view-class-period/{id}", name="app_class_school_view_class_period", methods={"POST", "GET"})
     *
     * @param ClassPeriod|null $classPeriod
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function viewClassPeriod(Request $request, ClassPeriod $classPeriod, ClassSchoolManager $schoolManager)
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            $students = $request->request->get('students', []);
            $result = $schoolManager->addStudentToClass($students, $classPeriod);

            if ($result) {
                $this->addFlash(
                    'info',
                    'Les élèves ont été ajouté à la Classe '.$classPeriod->getclassSchool()->getName().' pour la periode '.$classPeriod->getPeriod()->getName()
                );

                return $this->redirect($this->generateUrl('app_class_period_show', ['id' => $classPeriod->getId()]));
            } else {
                $this->addFlash('success', 'The student has not added');
            }
        }

        return $this->redirect($this->generateUrl('app_class_period_show_student', [
            'id' => $classPeriod->getId(),
        ]));
    }

    /**
     * @Route("/without-student", name="app_class_school_without_student", methods={"POST"}, options={"expose"=true})
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function withOutStudentAction()
    {
        $manager = $this->getDoctrine()->getManager();

        $periodSelected = $this->getPeriod();
        $school = $this->getSchool();

        $students = $manager->getRepository(Student::class)
            ->getListStudentsWithoutClassPeriod($periodSelected, $school);

        $aListStudent = [];
        foreach ($students as $student) {
            /** @var DateTime $birthday */
            $birthday = $student['birthday'];
            $age = $birthday->diff(new DateTime())->y;

            if (!array_key_exists($age, $aListStudent)) {
                $aListStudent[$age] = [];
            }

            $aListStudent[$age][] = $student;
        }

        ksort($aListStudent);

        return $this->render('class_period/students.html.twig', [
            'listStudents' => $aListStudent,
            'numberStudents' => count($students),
        ]);
    }
}

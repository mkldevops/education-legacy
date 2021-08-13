<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Teacher;
use App\Form\TeacherType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher")
 * @IsGranted("ROLE_DIRECTOR")
 */
class TeacherController extends AbstractBaseController
{
    /**
     * @Route("", name="app_teacher_index", methods={"GET"})
     */
    public function index(int $page = 1, string $search = ''): Response
    {
        $manager = $this->getDoctrine()->getManager();

        $search = addcslashes(urldecode($search), '%_');

        $count = $manager
            ->getRepository(Teacher::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        /** @var Teacher[] $teacherList */
        $teacherList = $manager
            ->getRepository(Teacher::class)
            ->createQueryBuilder('e')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('teacher/index.html.twig', [
            'teacherList' => $teacherList,
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
            ->setAction($this->generateUrl('app_teacher_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * @Route("create", name="app_teacher_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $teacher = new Teacher();

        $form = $this->createCreateForm($teacher)
            ->handleRequest($request);

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $teacher->setName($teacher->getPerson()->getNameComplete())
                ->setAuthor($this->getUser());
            $manager->persist($teacher);
            $manager->flush();

            $this->addFlash('success', 'The Teacher has been created.');

            return $this->redirect($this->generateUrl('app_teacher_show', ['id' => $teacher->getId()]));
        }

        return $this->render('teacher/new.html.twig', [
            'teacher' => $teacher,
            'form' => $form->createView(),
        ]);
    }

    private function createCreateForm(Teacher $teacher): FormInterface
    {
        $form = $this->createForm(TeacherType::class, $teacher, [
            'action' => $this->generateUrl('app_teacher_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * @Route("/new", name="app_teacher_new", methods={"GET"})
     */
    public function new()
    {
        $teacher = new Teacher();
        $form = $this->createCreateForm($teacher);

        return $this->render('teacher/new.html.twig', [
            'teacher' => $teacher,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Teacher entity.
     *
     * @Route("/show/{id}", name="app_teacher_show", methods={"GET"})
     *
     * @return Response
     */
    public function show(Teacher $teacher)
    {
        $person = $teacher->getPerson();

        return $this->render('teacher/show.html.twig', [
            'teacher' => $teacher,
            'person' => $person,
        ]);
    }

    /**
     * Displays a form to edit an existing Teacher entity.
     *
     * @Route("/edit/{id}", name="app_teacher_edit", methods={"GET"})
     *
     * @return Response
     */
    public function editAction(Teacher $teacher)
    {
        $editForm = $this->createEditForm($teacher);

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a Teacher entity.
     *
     * @param Teacher $teacher The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(Teacher $teacher)
    {
        $form = $this->createForm(TeacherType::class, $teacher, [
            'action' => $this->generateUrl('app_teacher_update', ['id' => $teacher->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing Teacher entity.
     *
     * @Route("/update/{id}", name="app_teacher_update", methods={"PUT", "POST"})
     *
     * @return RedirectResponse|Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        if (empty($teacher)) {
            throw $this->createNotFoundException('Unable to find Teacher entity.');
        }

        $editForm = $this->createEditForm($teacher);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->flush();

            $this->addFlash('success', 'The Teacher has been updated.');

            return $this->redirect($this->generateUrl('app_teacher_show', ['id' => $teacher->getId()]));
        }

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Teacher entity.
     *
     * @Route("/delete/{id}", name="app_teacher_delete", methods={"GET", "DELETE"})
     *
     * @return RedirectResponse|Response
     */
    public function delete(Request $request, Teacher $teacher)
    {
        $deleteForm = $this->createDeleteForm($teacher->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            if (empty($teacher)) {
                throw $this->createNotFoundException('Unable to find Teacher entity.');
            }

            $manager = $this->getDoctrine()->getManager();
            $personDeleteRequest = $request->get('personDelete');

            if ($personDeleteRequest) {
                $manager->remove($teacher->getPerson());
            }

            $manager->remove($teacher);
            $manager->flush();

            $this->addFlash('success', 'The Teacher has been deleted.');

            return $this->redirect($this->generateUrl('app_teacher_index'));
        }

        return $this->render('teacher/delete.html.twig', [
            'teacher' => $teacher,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a Teacher entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(int $id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_teacher_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('personDelete', CheckboxType::class, [
                'label' => 'Delete entity person linked with this teacher',
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @Route("/search", name="app_teacher_search", methods={"GET"})
     */
    public function searchAction(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_teacher_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

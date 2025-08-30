<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Teacher;
use App\Form\TeacherType;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;

#[AttributeIsGranted('ROLE_DIRECTOR')]
class TeacherController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route(path: '/teacher', name: 'app_teacher_index', methods: ['GET'])]
    public function index(TeacherRepository $teacherRepository, int $page = 1, string $search = ''): Response
    {
        $search = addcslashes(urldecode($search), '%_');
        $count = $teacherRepository->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $pages = ceil($count / 20);

        /** @var Teacher[] $teacherList */
        $teacherList = $teacherRepository
            ->createQueryBuilder('e')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;

        return $this->render('teacher/index.html.twig', [
            'teacherList' => $teacherList,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }

    #[Route(path: '/teachercreate', name: 'app_teacher_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teacher = new Teacher();
        $form = $this->createCreateForm($teacher)
            ->handleRequest($request)
        ;
        if ($form->isValid()) {
            $teacher->setName($teacher->getPerson()?->getNameComplete());
            $entityManager->persist($teacher);
            $entityManager->flush();

            $this->addFlash('success', 'The Teacher has been created.');

            return $this->redirectToRoute('app_teacher_show', ['id' => $teacher->getId()]);
        }

        return $this->render('teacher/new.html.twig', [
            'teacher' => $teacher,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/teacher/new', name: 'app_teacher_new', methods: ['GET'])]
    public function new(): Response
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
     */
    #[Route(path: '/teacher/show/{id}', name: 'app_teacher_show', methods: ['GET'])]
    public function show(Teacher $teacher): Response
    {
        $person = $teacher->getPerson();

        return $this->render('teacher/show.html.twig', [
            'teacher' => $teacher,
            'person' => $person,
        ]);
    }

    /**
     * Displays a form to edit an existing Teacher entity.
     */
    #[Route(path: '/teacher/edit/{id}', name: 'app_teacher_edit', methods: ['GET'])]
    public function edit(Teacher $teacher): Response
    {
        $editForm = $this->createEditForm($teacher);

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * Edits an existing Teacher entity.
     */
    #[Route(path: '/teacher/update/{id}', name: 'app_teacher_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, Teacher $teacher, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($teacher);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Teacher has been updated.');

            return $this->redirectToRoute('app_teacher_show', ['id' => $teacher->getId()]);
        }

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/teacher/delete/{id}', name: 'app_teacher_delete', methods: [Request::METHOD_GET, Request::METHOD_DELETE])]
    public function delete(Request $request, Teacher $teacher, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($teacher->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $personDeleteRequest = $request->get('personDelete');

            if ($personDeleteRequest) {
                $entityManager->remove($teacher->getPerson());
            }

            $entityManager->remove($teacher);
            $entityManager->flush();

            $this->addFlash('success', 'The Teacher has been deleted.');

            return $this->redirectToRoute('app_teacher_index');
        }

        return $this->render('teacher/delete.html.twig', [
            'teacher' => $teacher,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/teacher/search', name: 'app_teacher_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirectToRoute('app_teacher_index', [
            'page' => 1,
            'search' => urlencode((string) $all['form']['q']),
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
            ->getForm()
        ;
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
     * Creates a form to edit a Teacher entity.
     *
     * @param Teacher $teacher The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(Teacher $teacher): FormInterface
    {
        $form = $this->createForm(TeacherType::class, $teacher, [
            'action' => $this->generateUrl('app_teacher_update', ['id' => $teacher->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_teacher_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('personDelete', CheckboxType::class, [
                'label' => 'Delete entity person linked with this teacher',
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}

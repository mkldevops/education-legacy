<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\StudentComment;
use App\Form\StudentCommentType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/student-comment')]
class StudentCommentController extends AbstractBaseController
{
    /**
     * @throws NonUniqueResultException|NoResultException
     */
    #[Route('/{page}/{search}', name: 'app_student_comment_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = ''): Response
    {
        $em = $this->getDoctrine()->getManager();

        $count = $em
            ->getRepository(StudentComment::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->innerJoin('e.author', 'a')
            ->innerJoin('e.student', 's')
            ->where('e.title LIKE :title')
            ->setParameter(':title', '%' . $search . '%')
            ->orWhere('e.text LIKE :text')
            ->setParameter(':text', '%' . $search . '%')
            ->orWhere('e.type LIKE :type')
            ->setParameter(':type', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        $studentCommentList = $em
            ->getRepository(StudentComment::class)
            ->createQueryBuilder('e')
            ->where('e.title LIKE :title')
            ->setParameter(':title', '%' . $search . '%')
            ->orWhere('e.text LIKE :text')
            ->setParameter(':text', '%' . $search . '%')
            ->orWhere('e.type LIKE :type')
            ->setParameter(':type', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('student_comment/index.html.twig', [
            'studentcommentList' => $studentCommentList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }

    /**
     * Creates a form to search StudentComment entities.
     *
     *
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_student_comment_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * Creates a new StudentComment entity.
     */
    #[Route(path: '/create', name: 'app_student_comment_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|Response
    {
        $studentComment = new StudentComment();
        $form = $this->createCreateForm($studentComment);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($studentComment);
            $em->flush();

            $this->addFlash('success', 'The StudentComment has been created.');

            return $this->redirect($this->generateUrl('student-comment_show', ['id' => $studentComment->getId()]));
        }
        return $this->render('student_comment/new.html.twig', [
            'studentcomment' => $studentComment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a StudentComment entity.
     *
     * @param StudentComment $studentComment The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(StudentComment $studentComment): FormInterface
    {
        $form = $this->createForm(StudentCommentType::class, $studentComment, [
            'action' => $this->generateUrl('app_student_comment_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new StudentComment entity.
     */
    #[Route(path: '/new', name: 'app_student_comment_new', methods: ['GET'])]
    public function new(): Response
    {
        $studentComment = new StudentComment();
        $form = $this->createCreateForm($studentComment);
        return $this->render('student_comment/new.html.twig', [
            'studentcomment' => $studentComment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a StudentComment entity.
     */
    #[Route(path: '/show/{id}', name: 'app_student_comment_show', methods: ['GET'])]
    public function show(StudentComment $studentComment): Response
    {
        return $this->render('student_comment/show.html.twig', [
            'studentcomment' => $studentComment,
        ]);
    }

    /**
     * Displays a form to edit an existing StudentComment entity.
     */
    #[Route(path: '/edit/{id}', name: 'app_student_comment_edit', methods: ['GET'])]
    public function edit(StudentComment $studentComment): Response
    {
        $editForm = $this->createEditForm($studentComment);
        return $this->render('student_comment/edit.html.twig', [
            'studentcomment' => $studentComment,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a StudentComment entity.
     *
     * @param StudentComment $studentComment The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(StudentComment $studentComment): FormInterface
    {
        $form = $this->createForm(StudentCommentType::class, $studentComment, [
            'action' => $this->generateUrl('app_student_comment_update', ['id' => $studentComment->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing StudentComment entity.
     */
    #[Route(path: '/update/{id}', name: 'app_student_comment_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, StudentComment $studentComment): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($studentComment);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The StudentComment has been updated.');

            return $this->redirect($this->generateUrl('app_student_comment_show', ['id' => $studentComment->getId()]));
        }
        return $this->render('student_comment/edit.html.twig', [
            'studentcomment' => $studentComment,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a StudentComment entity.
     */
    #[Route(path: '/delete/{id}', name: 'app_student_comment_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, StudentComment $studentComment): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($studentComment->getId());
        $deleteForm->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($deleteForm->isValid()) {
            $em->remove($studentComment);
            $em->flush();

            $this->addFlash('success', 'The StudentComment has been deleted.');

            return $this->redirect($this->generateUrl('app_student_show', ['id' => $studentComment->getStudent()->getId()]));
        }
        return $this->render('student_comment/delete.html.twig', [
            'studentcomment' => $studentComment,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a StudentComment entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_student_comment_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_student_comment_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();
        return $this->redirect($this->generateUrl('app_student_comment_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

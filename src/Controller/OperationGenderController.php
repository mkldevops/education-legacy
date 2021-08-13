<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\OperationGender;
use App\Form\OperationGenderType;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/operation-gender")
 * @IsGranted("ROLE_ACCOUNTANT")
 */
class OperationGenderController extends AbstractBaseController
{
    /**
     * Lists all OperationGender entities.
     *
     * @Route("/list/{page}/{search}", name="app_operation_gender_index", methods={"GET"})
     *
     * @param string $search
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function indexAction(int $page = 1, $search = '')
    {
        $em = $this->getDoctrine()->getManager();

        $count = $em
            ->getRepository(OperationGender::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.code LIKE :code')
            ->setParameter(':code', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        $operationGenderList = $em
            ->getRepository(OperationGender::class)
            ->createQueryBuilder('e')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.code LIKE :code')
            ->setParameter(':code', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('OperationGender:index.html.twig', [
            'operationgenderList' => $operationGenderList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }

    /**
     * Creates a form to search OperationGender entities.
     *
     * @param string $q
     *
     * @return Form|FormInterface
     */
    private function createSearchForm($q = '')
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_operation_gender_search'))
            ->setMethod('post')
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * Creates a new OperationGender entity.
     *
     * @return RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $operationGender = new OperationGender();
        $form = $this->createCreateForm($operationGender);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($operationGender);
            $em->flush();

            $this->addFlash('success', 'The OperationGender has been created.');

            return $this->redirectToRoute('app_operation_gender_show', ['id' => $operationGender->getId()]);
        }

        return $this->render('OperationGender:new.html.twig', [
            'operationgender' => $operationGender,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a OperationGender entity.
     *
     * @param OperationGender $operationGender The entity
     *
     * @return FormInterface
     */
    private function createCreateForm(OperationGender $operationGender)
    {
        $form = $this->createForm(new OperationGenderType(), $operationGender, [
            'action' => $this->generateUrl('app_operation_gender_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new OperationGender entity.
     */
    public function new()
    {
        $operationGender = new OperationGender();
        $form = $this->createCreateForm($operationGender);

        return $this->render('OperationGender:new.html.twig', [
            'operationgender' => $operationGender,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a OperationGender entity.
     *
     * @return Response
     */
    public function show(OperationGender $operationGender)
    {
        return $this->render('OperationGender:show.html.twig', [
            'operationgender' => $operationGender,
        ]);
    }

    /**
     * Displays a form to edit an existing OperationGender entity.
     *
     * @return Response
     */
    public function editAction(Request $request, OperationGender $operationGender)
    {
        $editForm = $this->createEditForm($operationGender);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            $this->addFlash('success', 'Operation Gender is updated');
        }

        return $this->render('OperationGender:edit.html.twig', [
            'operationgender' => $operationGender,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a OperationGender entity.
     *
     * @param OperationGender $operationGender The entity
     *
     * @return FormInterface
     */
    private function createEditForm(OperationGender $operationGender)
    {
        $form = $this->createForm(new OperationGenderType(), $operationGender, [
            'action' => $this->generateUrl('app_operation_gender_update', ['id' => $operationGender->getId()]),
            'method' => 'PUT',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing OperationGender entity.
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $operationGender = $em->getRepository('OperationGender')->find($id);

        if (null === $operationGender) {
            throw $this->createNotFoundException('Unable to find OperationGender entity.');
        }

        $editForm = $this->createEditForm($operationGender);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', 'The OperationGender has been updated.');

            return $this->redirect($this->generateUrl('app_operation_gender_show', ['id' => $id]));
        }

        return $this->render('OperationGender:edit.html.twig', [
            'operationgender' => $operationGender,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a OperationGender entity.
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request, OperationGender $operationGender)
    {
        $deleteForm = $this->createDeleteForm($operationGender);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isValid()) {
            $em = $this->getManager();
            $em->remove($operationGender);
            $em->flush();

            $this->addFlash('success', 'The OperationGender has been deleted.');

            return $this->redirect($this->generateUrl('app_operation_gender'));
        }

        return $this->render('OperationGender:delete.html.twig', [
            'operationgender' => $operationGender,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a OperationGender entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return FormInterface
     */
    private function createDeleteForm(OperationGender $operationGender)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_operation_gender_delete', ['id' => $operationGender->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @return RedirectResponse
     */
    public function searchAction(Request $request)
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_operation_gender', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

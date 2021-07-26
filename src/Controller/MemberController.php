<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Member controller.
 *
 * @Route("/member")
 * @IsGranted("ROLE_MEMBER")
 */
class MemberController extends BaseController
{
    /**
     * @Route("", name="app_member_index", methods={"GET"})
     */
    public function index(MemberRepository $repository, int $page = 1, string $search = ''): Response
    {
        $search = addcslashes(urldecode($search), '%_');

        $count = $repository->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.positionName LIKE :function')
            ->setParameter('function', '%'.$search.'%')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        $memberList = $repository->createQueryBuilder('e')
            ->where('e.positionName LIKE :function')
            ->setParameter('function', '%'.$search.'%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('member/index.html.twig', [
            'memberList' => $memberList,
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
            ->setAction($this->generateUrl('app_member_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * @Route("/new", name="app_member_new", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(): Response
    {
        $member = new Member();
        $form = $this->createCreateForm($member);

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a Member entity.
     *
     * @param Member $member The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(Member $member): FormInterface
    {
        $form = $this->createForm(MemberType::class, $member, [
            'action' => $this->generateUrl('app_member_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * @Route("/create", name="app_member_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $member = new Member();
        $form = $this->createCreateForm($member)
            ->handleRequest($request);

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $member->setStructure($this->getEntitySchool()->getStructure())
                ->setAuthor($this->getUser());

            $manager->persist($member);
            $manager->flush();

            $this->addFlash('success', 'The Member has been created.');

            return $this->redirect($this->generateUrl('app_member_show', ['id' => $member->getId()]));
        }

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="app_member_show", methods={"GET"})
     */
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', ['member' => $member]);
    }

    /**
     * @Route("/edit/{id}", name="app_member_edit", methods={"GET"})
     */
    public function edit(Member $member): Response
    {
        $editForm = $this->createEditForm($member);

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'edit_form' => $editForm->createView(),
        ]);
    }

    private function createEditForm(Member $member): FormInterface
    {
        $form = $this->createForm(MemberType::class, $member, [
            'action' => $this->generateUrl('app_member_update', ['id' => $member->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * @Route("/update/{id}", name="app_member_update", methods={"POST", "PUT"})
     */
    public function update(Request $request, Member $member): Response
    {
        $editForm = $this->createEditForm($member);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            $this->addFlash('success', 'The Member has been updated.');

            return $this->redirect($this->generateUrl('app_member_show', ['id' => $member->getId()]));
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_member_delete", methods={"GET", "DELETE"})
     */
    public function delete(Request $request, Member $member): Response
    {
        $deleteForm = $this->createDeleteForm($member->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($member);
            $manager->flush();

            $this->addFlash('success', 'The Member has been deleted.');

            return $this->redirect($this->generateUrl('app_member_index'));
        }

        return $this->render('member/delete.html.twig', [
            'member' => $member,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_member_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * @Route("/search", name="app_member_search", methods={"GET"})
     */
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_member_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

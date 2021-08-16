<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Package;
use App\Form\PackageType;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Package controller.
 *
 * @Route("/package")
 */
class PackageController extends AbstractBaseController
{
    /**
     * Lists all Package entities.
     *
     * @Route("/list/{page}/{search}", name="app_package_index", methods={"GET"})
     *
     * @throws NonUniqueResultException
     */
    public function index(int $page = 1, string $search = ''): Response
    {
        $em = $this->getDoctrine()->getManager();

        $count = $em
            ->getRepository(Package::class)
            ->getQueryBuilder($search, $this->isGranted('ROLE_SUPER_ADMIN') ? null : $this->getSchool())
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        /** @var Package[] $packageList */
        $packageList = $em
            ->getRepository(Package::class)
            ->getQueryBuilder($search, $this->isGranted('ROLE_SUPER_ADMIN') ? null : $this->getSchool())
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('package/index.html.twig', [
            'packageList' => $packageList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }

    /**
     * Creates a form to search Package entities.
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_package_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * Displays a form to create a new Package entity.
     *
     * @Route("/new", name="app_package_new", methods={"GET"})
     */
    public function new(): Response
    {
        $package = new Package();
        $form = $this->createCreateForm($package);

        return $this->render('package/new.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a Package entity.
     *
     * @param Package $package The entity
     */
    private function createCreateForm(Package $package): FormInterface
    {
        $form = $this->createForm(PackageType::class, $package, [
            'action' => $this->generateUrl('app_package_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $form->remove('school');
        }

        return $form;
    }

    /**
     * Creates a new Package entity.
     *
     * @Route("/create", name="app_package_create", methods={"POST"})
     *
     * @return RedirectResponse|Response
     */
    public function create(Request $request): Response
    {
        $package = new Package();
        $form = $this->createCreateForm($package);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
                $package->setSchool($this->getEntitySchool());
            }

            $em->persist($package);
            $em->flush();

            $this->addFlash(
                'success',
                'The Package has been created.'
            );

            return $this->redirect($this->generateUrl('app_package_show', ['id' => $package->getId()]));
        }

        return $this->render('package/new.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Package entity.
     *
     * @Route("/show/{id}", name="app_package_show", methods={"GET"})
     */
    public function show(Package $package): Response
    {
        return $this->render('package/show.html.twig', [
            'package' => $package,
        ]);
    }

    /**
     * Displays a form to edit an existing Package entity.
     *
     * @Route("/edit/{id}", name="app_package_edit", methods={"GET"})
     */
    public function edit(Package $package): Response
    {
        $editForm = $this->createEditForm($package);

        return $this->render('package/edit.html.twig', [
            'package' => $package,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a Package entity.
     *
     * @param Package $package The entity
     */
    private function createEditForm(Package $package): FormInterface
    {
        $form = $this->createForm(PackageType::class, $package, [
            'action' => $this->generateUrl('app_package_update', ['id' => $package->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $form->remove('school');
        }

        return $form;
    }

    /**
     * Edits an existing Package entity.
     *
     * @Route("/update/{}id", name="app_package_update", methods={"POST"})
     *
     * @return RedirectResponse|Response
     */
    public function update(Request $request, Package $package): Response
    {
        $editForm = $this->createEditForm($package);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The Package has been updated.');

            return $this->redirect($this->generateUrl('app_package_show', ['id' => $package->getId()]));
        }

        return $this->render('package/edit.html.twig', [
            'package' => $package,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Package entity.
     *
     * @Route("/delete/{id}", name="app_package_delete", methods={"GET", "POST"})
     */
    public function delete(Request $request, Package $package): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $deleteForm = $this->createDeleteForm($package->getId())
            ->handleRequest($request);

        if ($deleteForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($package);
            $em->flush();

            $this->addFlash(
                'success',
                'The Package has been deleted.'
            );

            return $this->redirect($this->generateUrl('app_package'));
        }

        return $this->render('package/delete.html.twig', [
            'package' => $package,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a Package entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder()
            ->set($this->generateUrl('app_package_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @Route("/search", name="app_package_search", methods={"POST"})
     */
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_package', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

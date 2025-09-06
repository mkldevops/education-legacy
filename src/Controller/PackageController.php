<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Exception\AppException;
use App\Form\PackageType;
use App\Manager\SchoolManager;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PackageController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     * @throws AppException
     * @throws NoResultException
     */
    #[Route(path: '/package/list/{page}/{search}', name: 'app_package_index', methods: ['GET'])]
    public function index(PackageRepository $packageRepository, SchoolManager $schoolManager, int $page = 1, string $search = ''): Response
    {
        $count = $packageRepository
            ->getQueryBuilder($search, $this->isGranted('ROLE_SUPER_ADMIN') ? null : $schoolManager->getSchool())
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $pages = ceil($count / 20);
        /** @var Package[] $packageList */
        $packageList = $packageRepository
            ->getQueryBuilder($search, $this->isGranted('ROLE_SUPER_ADMIN') ? null : $schoolManager->getSchool())
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;

        return $this->render('package/index.html.twig', [
            'packageList' => $packageList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }

    /**
     * Displays a form to create a new Package entity.
     */
    #[Route(path: '/package/new', name: 'app_package_new', methods: ['GET'])]
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
     * @throws AppException
     */
    #[Route(path: '/package/create', name: 'app_package_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SchoolManager $schoolManager): Response
    {
        $package = new Package();
        $form = $this->createCreateForm($package);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
                $package->setSchool($schoolManager->getEntitySchool());
            }

            $entityManager->persist($package);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The Package has been created.'
            );

            return $this->redirectToRoute('app_package_show', ['id' => $package->getId()]);
        }

        return $this->render('package/new.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Package entity.
     */
    #[Route(path: '/package/show/{id}', name: 'app_package_show', methods: ['GET'])]
    public function show(Package $package): Response
    {
        return $this->render('package/show.html.twig', [
            'package' => $package,
        ]);
    }

    /**
     * Displays a form to edit an existing Package entity.
     */
    #[Route(path: '/package/edit/{id}', name: 'app_package_edit', methods: ['GET'])]
    public function edit(Package $package): Response
    {
        $editForm = $this->createEditForm($package);

        return $this->render('package/edit.html.twig', [
            'package' => $package,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/package/update/{id}', name: 'app_package_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($package);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Package has been updated.');

            return $this->redirectToRoute('app_package_show', ['id' => $package->getId()]);
        }

        return $this->render('package/edit.html.twig', [
            'package' => $package,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Package entity.
     */
    #[Route(path: '/package/delete/{id}', name: 'app_package_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Package $package, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($package->getId())
            ->handleRequest($request)
        ;
        if ($deleteForm->isValid()) {
            $entityManager->remove($package);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The Package has been deleted.'
            );

            return $this->redirectToRoute('app_package_index');
        }

        return $this->render('package/delete.html.twig', [
            'package' => $package,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/package/search', name: 'app_package_search', methods: ['POST'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirectToRoute('app_package_index', [
            'page' => 1,
            'search' => urlencode((string) $all['form']['q']),
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
            ->getForm()
        ;
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

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_package_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}

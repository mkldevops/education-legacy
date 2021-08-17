<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\PackageStudentPeriod;
use App\Form\PackageStudentPeriodType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PackageStudentPeriod controller.
 */
#[Route(path: '/package-student-period')]
class PackageStudentPeriodController extends AbstractBaseController
{
    /**
     * Lists all PackageStudentPeriod entities.
     */
    #[Route(path: '/list/{page}/{search}', name: 'app_package_student_period_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = '') : Response
    {
        $em = $this->getDoctrine()->getManager();
        $count = $em
            ->getRepository(PackageStudentPeriod::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.amount LIKE :amount')
            ->setParameter(':amount', '%' . $search . '%')
            ->orWhere('e.discount LIKE :discount')
            ->setParameter(':discount', '%' . $search . '%')
            ->orWhere('e.paid LIKE :paid')
            ->setParameter(':paid', '%' . $search . '%')
            ->orWhere('e.comment LIKE :comment')
            ->setParameter(':comment', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
        $pages = ceil($count / 20);
        $packageStudentPeriodList = $em
            ->getRepository(PackageStudentPeriod::class)
            ->createQueryBuilder('e')
            ->where('e.amount LIKE :amount')
            ->setParameter(':amount', '%' . $search . '%')
            ->orWhere('e.discount LIKE :discount')
            ->setParameter(':discount', '%' . $search . '%')
            ->orWhere('e.paid LIKE :paid')
            ->setParameter(':paid', '%' . $search . '%')
            ->orWhere('e.comment LIKE :comment')
            ->setParameter(':comment', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        return $this->render('package_student_period/index.html.twig', [
            'packagestudentperiodList' => $packageStudentPeriodList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }
    /**
     * Creates a form to search PackageStudentPeriod entities.
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_package_student_period_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }
    /**
     * Creates a new PackageStudentPeriod entity.
     *
     *
     * @return RedirectResponse|Response
     */
    #[Route(path: '/create', name: 'app_package_student_period_create', methods: ['POST'])]
    public function create(Request $request) : Response
    {
        $packageStudentPeriod = new PackageStudentPeriod();
        $form = $this->createCreateForm($packageStudentPeriod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($packageStudentPeriod);
            $em->flush();

            $this->addFlash('success', 'The package has been added to student.');

            return $this->redirect($this->generateUrl(
                'app_package_student_period_show',
                ['id' => $packageStudentPeriod->getId()]
            ));
        }
        return $this->render('package_student_period/new.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Creates a form to create a PackageStudentPeriod entity.
     *
     * @param PackageStudentPeriod $packageStudentPeriod The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(PackageStudentPeriod $packageStudentPeriod): FormInterface
    {
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_package_student_period_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }
    /**
     * Displays a form to create a new PackageStudentPeriod entity.
     */
    #[Route(path: '/new', name: 'app_package_student_period_new', methods: ['GET'])]
    public function new() : Response
    {
        $packageStudentPeriod = new PackageStudentPeriod();
        $form = $this->createCreateForm($packageStudentPeriod);
        return $this->render('package_student_period/new.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Finds and displays a PackageStudentPeriod entity.
     */
    #[Route(path: '/show/{id}', name: 'app_package_student_period_show', methods: ['GET'])]
    public function show(PackageStudentPeriod $packageStudentPeriod) : Response
    {
        return $this->render('package_student_period/show.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
        ]);
    }
    /**
     * Displays a form to edit an existing PackageStudentPeriod entity.
     */
    #[Route(path: '/edit/{id}', name: 'app_package_student_period_edit', methods: ['GET'])]
    public function edit(PackageStudentPeriod $packageStudentPeriod) : Response
    {
        $editForm = $this->createEditForm($packageStudentPeriod);
        return $this->render('package_student_period/edit.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Creates a form to edit a PackageStudentPeriod entity.
     *
     * @param PackageStudentPeriod $packageStudentPeriod The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(PackageStudentPeriod $packageStudentPeriod): FormInterface
    {
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod, [
            'action' => $this->generateUrl('app_package_student_period_update', ['id' => $packageStudentPeriod->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->remove('period')
            ->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }
    /**
     * Edits an existing PackageStudentPeriod entity.
     *
     *
     * @return RedirectResponse|Response
     */
    #[Route(path: '/update/{id}', name: 'app_package_student_period_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, PackageStudentPeriod $packageStudentPeriod) : Response
    {
        $editForm = $this->createEditForm($packageStudentPeriod);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->getManager()->flush();
            $this->addFlash('success', 'The PackageStudentPeriod has been updated.');

            return $this->redirect($this->generateUrl('app_package_student_period_show', ['id' => $packageStudentPeriod->getId()]));
        }
        return $this->render('package_student_period/edit.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Deletes a PackageStudentPeriod entity.
     *
     *
     * @return RedirectResponse|Response
     */
    #[Route(path: '/delete/{id}', name: 'app_package_student_period_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, PackageStudentPeriod $packageStudentPeriod) : Response
    {
        $deleteForm = $this->createDeleteForm($packageStudentPeriod->getId());
        $deleteForm->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $em->remove($packageStudentPeriod);
            $em->flush();

            $this->addFlash('success', 'The PackageStudentPeriod has been deleted.');

            return $this->redirect($this->generateUrl('app_package_student_period_index'));
        }
        return $this->render('package_student_period/delete.html.twig', [
            'packagestudentperiod' => $packageStudentPeriod,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
    /**
     * Creates a form to delete a PackageStudentPeriod entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl(
                'app_package_student_period_delete',
                ['id' => $id]
            ))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }
    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_package_student_period_search', methods: ['GET'])]
    public function search(Request $request) : RedirectResponse
    {
        $all = $request->request->all();
        return $this->redirect($this->generateUrl('app_package_student_period_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

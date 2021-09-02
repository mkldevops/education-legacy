<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\PackageStudentPeriod;
use App\Entity\PaymentPackageStudent;
use App\Entity\TypeOperation;
use App\Form\PaymentPackageStudentType;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'payment-package-student')]
class PaymentPackageStudentController extends AbstractBaseController
{
    /**
     * Lists all PaymentPackageStudent entities.
     *
     * @throws NonUniqueResultException
     */
    /**
     * Lists all PaymentPackageStudent entities.
     *
     * @throws NonUniqueResultException
     */
    #[Route(path: '', name: 'app_payment_package_student_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = ''): Response
    {
        $manager = $this->getDoctrine()->getManager();
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        // Get the total entries.
        $count = $manager
            ->getRepository(PaymentPackageStudent::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.comment LIKE :comment')
            ->setParameter(':comment', '%'.$search.'%')
            ->orWhere('e.status LIKE :status')
            ->setParameter(':status', '%'.$search.'%')
            ->getQuery()
            ->getSingleScalarResult();
        // Define the number of pages.
        $pages = ceil($count / 20);
        // Get the entries of current page.
        $paymentPackageStudentList = $manager
            ->getRepository(PaymentPackageStudent::class)
            ->createQueryBuilder('e')
            ->where('e.comment LIKE :comment')
            ->setParameter(':comment', '%'.$search.'%')
            ->orWhere('e.status LIKE :status')
            ->setParameter(':status', '%'.$search.'%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('payment_package_student/index.html.twig', [
            'paymentpackagestudentList' => $paymentPackageStudentList,
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
            ->setAction($this->generateUrl('app_payment_package_student_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', SearchType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    #[Route('/create/{id}', name: 'app_payment_package_student_create', methods: ['GET', 'POST'])]
    public function create(Request $request, PackageStudentPeriod $packageStudentPeriod): Response
    {
        $paymentPackageStudent = new PaymentPackageStudent();
        $form = $this->createCreateForm($paymentPackageStudent, $packageStudentPeriod);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $typeOperation = $manager->getRepository(TypeOperation::class)
                ->findOneBy(['code' => TypeOperation::TYPE_CODE_PAYMENT_PACKAGE_STUDENT]);

            $paymentPackageStudent->setPackageStudentPeriod($packageStudentPeriod);

            $user = $this->getUser();
            $paymentPackageStudent->getOperation()->setPublisher($user);
            $paymentPackageStudent->getOperation()->setTypeOperation($typeOperation);

            if (null === $paymentPackageStudent->getOperation()->getDate()) {
                $planned = $paymentPackageStudent->getOperation()->getDatePlanned();
                $paymentPackageStudent->getOperation()->setDate($planned);
            }

            $paymentPackageStudent->getOperation()
                ->setName(sprintf(
                    '%s - %s ',
                    $packageStudentPeriod->getStudent()?->getNameComplete(),
                    $packageStudentPeriod->getPeriod()?->getName()
                ));

            $manager->persist($paymentPackageStudent);
            $manager->flush();

            $this->addFlash('success', 'The PaymentPackageStudent has been created.');

            return $this->redirect($this->generateUrl('app_student_show', [
                'id' => $packageStudentPeriod->getStudent()->getId(),
            ]));
        }

        return $this->render('payment_package_student/new.html.twig', [
            'paymentpackagestudent' => $paymentPackageStudent,
            'form' => $form->createView(),
        ]);
    }

    private function createCreateForm(
        PaymentPackageStudent $paymentPackageStudent,
        PackageStudentPeriod $packageStudentPeriod
    ): FormInterface {
        $form = $this->createForm(PaymentPackageStudentType::class, $paymentPackageStudent, [
            'action' => $this->generateUrl('app_payment_package_student_create', [
                'id' => $packageStudentPeriod->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new PaymentPackageStudent entity.
     */
    #[Route(path: '/new/{id}', name: 'app_payment_package_student_new', methods: ['GET'])]
    public function new(PackageStudentPeriod $packageStudentPeriod): Response
    {
        $paymentPackageStudent = new PaymentPackageStudent();
        $form = $this->createCreateForm($paymentPackageStudent, $packageStudentPeriod);

        return $this->render('payment_package_student/new.html.twig', [
            'paymentPackageStudent' => $paymentPackageStudent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new-student/{id}', name: 'app_payment_package_student_new', methods: ['GET', 'POST'])]
    public function newStudent(PackageStudentPeriod $packageStudentPeriod): Response
    {
        $paymentPackageStudent = new PaymentPackageStudent();
        $form = $this->createCreateForm($paymentPackageStudent, $packageStudentPeriod);

        return $this->render('payment_package_student/newStudent.html.twig', [
            'packageStudentPeriod' => $packageStudentPeriod,
            'paymentPackageStudent' => $paymentPackageStudent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a PaymentPackageStudent entity.
     */
    #[Route(path: '/show/{id}', name: 'app_payment_package_student_show', methods: ['GET'])]
    public function show(PaymentPackageStudent $paymentPackageStudent): Response
    {
        return $this->render('payment_package_student/show.html.twig', [
            'paymentpackagestudent' => $paymentPackageStudent,
        ]);
    }

    /**
     * Displays a form to edit an existing PaymentPackageStudent entity.
     */
    #[Route(path: '/show/{id}', name: 'app_payment_package_student_show', methods: ['GET'])]
    public function edit(PaymentPackageStudent $paymentPackageStudent): Response
    {
        $editForm = $this->createEditForm($paymentPackageStudent);

        return $this->render('payment_package_student/edit.html.twig', [
            'paymentpackagestudent' => $paymentPackageStudent,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a PaymentPackageStudent entity.
     *
     * @param PaymentPackageStudent $paymentPackageStudent The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(PaymentPackageStudent $paymentPackageStudent): FormInterface
    {
        $form = $this->createForm(new PaymentPackageStudentType(), $paymentPackageStudent, [
            'action' => $this->generateUrl('app_payment_package_student_update', ['id' => $paymentPackageStudent->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing PaymentPackageStudent entity.
     */
    #[Route(path: '/update/{id}', name: 'app_payment_package_student_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, PaymentPackageStudent $paymentPackageStudent): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($paymentPackageStudent);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash('success', 'The PaymentPackageStudent has been updated.');

            return $this->redirect($this->generateUrl('app_payment_package_student_show', ['id' => $paymentPackageStudent->getId()]));
        }

        return $this->render('payment_package_student/edit.html.twig', [
            'paymentpackagestudent' => $paymentPackageStudent,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a PaymentPackageStudent entity.
     */
    #[Route(path: '/delete/{id}', name: 'app_payment_package_student_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, PaymentPackageStudent $paymentPackageStudent): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($paymentPackageStudent->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($paymentPackageStudent);
            $manager->flush();

            $this->addFlash(
                'success',
                'The PaymentPackageStudent has been deleted.'
            );

            return $this->redirect($this->generateUrl('app_payment_package_student_index'));
        }

        return $this->render('payment_package_student/delete.html.twig', [
            'paymentpackagestudent' => $paymentPackageStudent,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a PaymentPackageStudent entity by id.
     *
     * @param int $id The entity id
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_payment_package_student_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_payment_package_student_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_payment_package_student_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}

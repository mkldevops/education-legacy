<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\Validate;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\DocumentFetcher;
use App\Form\OperationType;
use App\Manager\OperationManager;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use App\Manager\StatisticsManager;
use App\Repository\AccountRepository;
use App\Repository\AccountStatementRepository;
use App\Repository\OperationRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ACCOUNTANT')]
class OperationController extends AbstractController
{
    public function __construct(
        protected readonly SchoolManager $schoolManager
    ) {}

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/operation/{page}', name: 'app_operation_index', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function index(OperationRepository $operationRepository, PeriodManager $periodManager, int $page = 1): Response
    {
        if ($page < 1) {
            $this->redirectToRoute('app_operation_index', ['page' => 1]);
        }

        $period = $periodManager->getPeriodsOnSession();
        $school = $this->schoolManager->getSchool();
        $formBuilder = $this->createFormBuilder()
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'choice_label' => 'name',
                'lable' => 'Compte',
            ])
        ;
        $operations = $operationRepository->getListOperations($period, $school);

        return $this->render('operation/index.html.twig', [
            'operations' => $operations,
            'formSearch' => $formBuilder,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/operation/new', name: 'app_operation_new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        $operation = new Operation();
        $params = [
            'account' => $request->get('account'),
            'accountstatement' => $request->get('accountstatement'),
        ];

        $operation->setDate(new \DateTime($request->get('date') ?? 'now'));
        $form = $this->createCreateForm($operation, $params);
        $form->handleRequest($request);

        return $this->render('operation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route('/operation/create', name : 'app_operation_create', methods: ['POST', 'PUT'])]
    public function create(
        Request $request,
        Security $security,
        AccountRepository $accountRepository,
        AccountStatementRepository $accountStatementRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $operation = new Operation();
        $form = $this->createCreateForm($operation, []);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($accountStatementRequest = $request->get('accountstatement', null)) {
                    $accountStatement = $accountStatementRepository->findOneBy(['id' => $accountStatementRequest]);
                    if (null !== $accountStatement) {
                        $operation->setAccountStatement($accountStatement);
                    }
                }

                if ($accountRequest = $request->get('account')) {
                    $account = $accountRepository->findOneBy(['id' => $accountRequest]);
                    if ($account instanceof Account) {
                        $operation->setAccount($account);
                    }
                }

                $entityManager->persist($operation);
                $entityManager->flush();

                $this->addFlash('success', 'The Operation has been created.');

                return $this->redirectToRoute('app_operation_show', ['id' => $operation->getId()]);
            }
        } catch (\Exception $exception) {
            $this->addFlash('danger', "The Operation haven't been created. because : ".$exception->getMessage());

            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $this->render('operation/new.html.twig', [
            'operation' => $operation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/operation/edit/{id}', name: 'app_operation_edit', methods: ['GET'])]
    public function edit(Operation $operation): Response
    {
        if (!$this->hasStructure($operation)) {
            return $this->redirectToRoute('app_operation_index');
        }

        return $this->render('operation/edit.html.twig', [
            'operation' => $operation,
            'edit_form' => $this->createEditForm($operation)->createView(),
        ]);
    }

    #[Route(path: '/operation/update/{id}', name: 'app_operation_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Operation $operation, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($operation);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->persist($operation);
            $entityManager->flush();

            $this->addFlash('success', 'The Operation has been updated.');

            return $this->redirectToRoute('app_operation_show', ['id' => $operation->getId()]);
        }

        return $this->render('operation/edit.html.twig', [
            'operation' => $operation,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/operation/show/{id}', name: 'app_operation_show', methods: ['GET'])]
    public function show(Operation $operation): Response
    {
        // Check if the school is good
        if (!$this->hasStructure($operation)) {
            return $this->redirectToRoute('app_operation_index');
        }

        return $this->render('operation/show.html.twig', [
            'operation' => $operation,
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/operation/stats-by-month', name: 'app_operation_statsbymonth', methods: ['GET'])]
    public function statsByMonthly(StatisticsManager $statisticsManager): Response
    {
        return $this->render('operation/stats_by_monthly.html.twig', [
            'stats' => $statisticsManager->getStatsByMonth(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/operation/delete/{id}', name: 'app_operation_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Operation $operation, EntityManagerInterface $entityManager): Response
    {
        $deleteForm = $this->createDeleteForm($operation->getId());
        $deleteForm->handleRequest($request);
        // Check if the school is good
        if (!$this->hasStructure($operation)) {
            return $this->redirectToRoute('app_operation_index');
        }

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $paymentPackageStudents = $operation->getPaymentPackageStudents();

            if (!$paymentPackageStudents->isEmpty()) {
                foreach ($paymentPackageStudents as $paymentPackageStudent) {
                    $entityManager->remove($paymentPackageStudent);
                }

                $entityManager->flush();
            }

            $entityManager->remove($operation);
            $entityManager->flush();

            $this->addFlash('success', "L'operation ".$operation->getId().' à été correctement supprimée');

            return $this->redirectToRoute('app_operation_index');
        }

        return $this->render('operation/delete.html.twig', [
            'operation' => $operation,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/operation/set-document/{id}/{action}', name: 'app_operation_set_document', methods: ['POST'])]
    public function setDocument(
        Request $request,
        Operation $operation,
        string $action,
        DocumentFetcher $documentFetcher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $responseModel = ResponseRequest::responseDefault();

        try {
            $document = $documentFetcher->getDocument($request->get('document'));

            if ('add' === $action) {
                $operation->addDocument($document);
            } elseif ('remove' === $action) {
                $operation->removeDocument($document);
            }

            $entityManager->persist($operation);
            $entityManager->flush();
        } catch (\Exception $exception) {
            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return new JsonResponse($responseModel);
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[IsGranted('ROLE_ACCOUNTANT')]
    #[Route('/operation/to-validate/{id}', name: 'app_operation_tovalidate', options: ['expose' => true], methods: ['GET'])]
    public function toValidate(OperationManager $operationManager): Response
    {
        return $this->render('operation/to_valiate.html.twig', [
            'operation' => $operationManager->toValidate(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[IsGranted('ROLE_ACCOUNTANT')]
    #[Route(path: '/operation/validate/{id}', name: 'app_operation_validate', options: ['expose' => true], methods: ['POST'])]
    public function validate(Operation $operation, Request $request, Security $security, EntityManagerInterface $entityManager, TranslatorInterface $translator): JsonResponse
    {
        $responseModel = ResponseRequest::responseDefault();

        try {
            if ($operation->getValidate() instanceof Validate) {
                throw new AppException('This operation is already validated');
            }

            if ($operationDate = $request->get('operation_date')) {
                $operation->setDate(\DateTime::createFromFormat('d/m/Y', $operationDate), true);
                $responseModel->data['operation_date'] = $operation->getDate()?->format('d/m/Y');
            }

            $validate = new Validate();
            $validate->setType(Validate::TYPE_SUCCESS);
            $validate->setMessage($translator->trans(
                'Validate operation : %id% - %name%',
                ['%id%' => $operation->getId(), '%name%' => $operation->getName()],
                'operation'
            ));

            $entityManager->persist($validate);
            $operation->setValidate($validate);
            $entityManager->persist($operation);
            $entityManager->flush();

            $responseModel->data['validate'] = $validate->getData();
            $responseModel->message = $translator->trans(
                'Validated by %name% <br />Date : %date%',
                [
                    '%name%' => $validate->getAuthor()?->getNameComplete() ?? 'unknow',
                    '%date%' => $validate->getCreatedAt()->format('d/m/Y H:i:s'),
                ],
                'operation'
            );
        } catch (\Exception $exception) {
            throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return new JsonResponse($responseModel);
    }

    private function createCreateForm(Operation $operation, array $params): FormInterface
    {
        $form = $this->createForm(OperationType::class, $operation, [
            'action' => $this->generateUrl('app_operation_create', $params),
            'method' => Request::METHOD_POST,
        ]);

        if (!empty($params['account'])) {
            $form->remove('account');
        }

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create']);

        return $form;
    }

    /**
     * @throws AppException
     */
    private function hasStructure(Operation $operation): bool
    {
        $result = true;

        if (!$operation->hasStructure($this->schoolManager->getSchool()->getStructure())) {
            $this->addFlash('danger', \sprintf(
                "Vous n'avez pas accès l'opération numero %s avec cette structure",
                $operation->getId()
            ));
            $result = false;
        }

        return $result;
    }

    private function createEditForm(Operation $operation): FormInterface
    {
        $form = $this->createForm(OperationType::class, $operation, [
            'action' => $this->generateUrl('app_operation_update', ['id' => $operation->getId()]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_operation_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete', 'attr' => ['class' => 'btn btn-default']])
            ->getForm()
        ;
    }
}

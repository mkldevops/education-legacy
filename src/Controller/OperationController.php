<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Operation;
use App\Entity\Validate;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\DocumentFetcher;
use App\Form\OperationType;
use App\Manager\OperationManager;
use App\Manager\SchoolManager;
use App\Manager\StatisticsManager;
use App\Repository\AccountRepository;
use App\Repository\AccountStatementRepository;
use App\Repository\OperationRepository;
use App\Services\ResponseRequest;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/operation')]
#[IsGranted('ROLE_ACCOUNTANT')]
class OperationController extends AbstractBaseController
{
    public function __construct(protected SchoolManager $schoolManager)
    {
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/{page}', name: 'app_operation_index', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function index(OperationRepository $repository, int $page = 1): Response
    {
        if ($page < 1) {
            $this->redirectToRoute('app_operation_index', ['page' => 1]);
        }
        $period = $this->getPeriod();
        $school = $this->schoolManager->getSchool();
        $formSearch = $this->createFormBuilder()
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'choice_label' => 'name',
                'lable' => 'Compte',
            ]);
        $operations = $repository->getListOperations($period, $school);

        return $this->render('operation/index.html.twig', [
            'operations' => $operations,
            'formSearch' => $formSearch,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/new', name: 'app_operation_new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        $operation = new Operation();
        $params = [
            'account' => $request->get('account'),
            'accountstatement' => $request->get('accountstatement'),
        ];
        $operation->setDate(new DateTime($request->get('date') ?? 'now'));
        $form = $this->createCreateForm($operation, $params);
        $form->handleRequest($request);

        return $this->render('operation/new.html.twig', [
            'form' => $form->createView(),
        ]);
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
    #[Route('/create', name : 'app_operation_create', methods: ['POST', 'PUT'])]
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
                    if (null !== $account) {
                        $operation->setAccount($account);
                    }
                }

                if (($user = $security->getUser()) !== null) {
                    $operation->setPublisher($user);
                }

                $entityManager->persist($operation);
                $entityManager->flush();

                $this->addFlash('success', 'The Operation has been created.');

                return $this->redirect($this->generateUrl('app_operation_show', ['id' => $operation->getId()]));
            }
        } catch (Exception $e) {
            $this->addFlash('danger', 'The Operation haven\'t been created. because : '.$e->getMessage());
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $this->render('operation/new.html.twig', [
            'operation' => $operation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'app_operation_edit', methods: ['GET'])]
    public function edit(Operation $operation): Response
    {
        if (!$this->hasStructure($operation)) {
            return $this->redirect($this->generateUrl('app_operation_index'));
        }

        return $this->render('operation/edit.html.twig', [
            'operation' => $operation,
            'edit_form' => $this->createEditForm($operation)->createView(),
        ]);
    }

    private function hasStructure(Operation $operation): bool
    {
        $result = true;

        if (!$operation->hasStructure($this->schoolManager->getSchool()?->getStructure())) {
            $this->addFlash('danger', sprintf(
                'Vous n\'avez pas accès l\'opération numero %s avec cette structure',
                $operation->getId() ?? 'undefined'
            ));
            $result = false;
        }

        return $result;
    }

    private function createEditForm(Operation $operation): FormInterface
    {
        $form = $this->createForm(OperationType::class, $operation, [
            'action' => $this->generateUrl('app_operation_update', ['id' => $operation->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    #[Route(path: '/update/{id}', name: 'app_operation_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Operation $operation): Response
    {
        $editForm = $this->createEditForm($operation);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($operation);
            $manager->flush();

            $this->addFlash('success', 'The Operation has been updated.');

            return $this->redirect($this->generateUrl('app_operation_show', ['id' => $operation->getId()]));
        }

        return $this->render('operation/edit.html.twig', [
            'operation' => $operation,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/show/{id}', name: 'app_operation_show', methods: ['GET'])]
    public function show(Operation $operation): Response
    {
        // Check if the school is good
        if (!$this->hasStructure($operation)) {
            return $this->redirect($this->generateUrl('app_operation_index'));
        }

        return $this->render('operation/show.html.twig', [
            'operation' => $operation,
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/stats-by-month', name: 'app_operation_statsbymonth', methods: ['GET'])]
    public function statsByMonthly(StatisticsManager $manager): Response
    {
        return $this->render('operation/stats_by_monthly.html.twig', [
            'stats' => $manager->getStatsByMonth(),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'app_operation_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Operation $operation): Response
    {
        $deleteForm = $this->createDeleteForm($operation->getId());
        $deleteForm->handleRequest($request);
        // Check if the school is good
        if (!$this->hasStructure($operation)) {
            return $this->redirect($this->generateUrl('app_operation_index'));
        }
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $paymentPackageStudents = $operation->getPaymentPackageStudents();
            $manager = $this->getDoctrine()->getManager();

            if (null !== $paymentPackageStudents) {
                foreach ($paymentPackageStudents as $payment) {
                    $manager->remove($payment);
                }
                $manager->flush();
            }

            $manager->remove($operation);
            $manager->flush();

            $this->addFlash('success', 'L\'operation '.$operation->getId().' à été correctement supprimée');

            return $this->redirect($this->generateUrl('app_operation_index'));
        }

        return $this->render('operation/delete.html.twig', [
            'operation' => $operation,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_operation_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete', 'attr' => ['class' => 'btn btn-default']])
            ->getForm();
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/set-document/{id}/{action}', name: 'app_operation_set_document', methods: ['POST'])]
    public function setDocument(
        Request $request,
        Operation $operation,
        string $action,
        DocumentFetcher $documentFetcher
    ): JsonResponse {
        $em = $this->getDoctrine()->getManager();
        $response = ResponseRequest::responseDefault();
        try {
            $document = $documentFetcher->getDocument($request->get('document'));

            if ('add' === $action) {
                $operation->addDocument($document);
            } elseif ('remove' === $action) {
                $operation->removeDocument($document);
            }

            $em->persist($operation);
            $em->flush();
        } catch (Exception $e) {
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new JsonResponse($response);
    }

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[IsGranted('ROLE_ACCOUNTANT')]
    #[Route('/to-validate/{id}', name: 'app_operation_tovalidate', options: ['expose' => true], methods: ['GET'])]
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
    #[Route(path: '/validate/{id}', name: 'app_operation_validate', options: ['expose' => true], methods: ['POST'])]
    public function validate(Operation $operation, Request $request, Security $security): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $response = ResponseRequest::responseDefault();
        try {
            if (null !== $operation->getValidate()) {
                throw new AppException('This operation is already validated');
            }

            if ($operationDate = $request->get('operation_date')) {
                $operation->setDate(DateTime::createFromFormat('d/m/Y', $operationDate), true);
                $response->data['operation_date'] = $operation->getDate()?->format('d/m/Y');
            }

            $validate = new Validate();
            $validate->setType(Validate::TYPE_SUCCESS);
            $validate->setMessage($this->trans(
                'Validate operation : %id% - %name%',
                ['%id%' => $operation->getId(), '%name%' => $operation->getName()],
                'operation'
            ));

            if (($user = $security->getUser()) !== null) {
                $validate->setAuthor($user);
            }

            $em->persist($validate);
            $operation->setValidate($validate);
            $em->persist($operation);
            $em->flush();

            $response->data['validate'] = $validate->getData();
            $response->message = $this->trans(
                'Validated by %name% <br />Date : %date%',
                [
                    '%name%' => $validate->getAuthor()?->getNameComplete() ?? 'unknow',
                    '%date%' => $validate->getCreatedAt()->format('d/m/Y H:i:s'),
                ],
                'operation'
            );
        } catch (Exception $e) {
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new JsonResponse($response);
    }
}

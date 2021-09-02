<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\AccountStatement;
use App\Entity\Operation;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\AccountStatementType;
use App\Repository\OperationRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/account-statement')]
class AccountStatementController extends AbstractBaseController
{

    #[Route(path: '/list/{page}/{search}', name: 'app_account_statement_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = ''): Response
    {
        $manager = $this->getDoctrine()->getManager();
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        // Get the total entries.
        $count = $manager
            ->getRepository(AccountStatement::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.title LIKE :title')
            ->setParameter(':title', '%' . $search . '%')
            ->orWhere('e.enable LIKE :enable')
            ->setParameter('enable', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
        // Define the number of pages.
        $pages = ceil($count / 20);
        // Get the entries of current page.
        /** @var AccountStatement[] $accountstatementList */
        $accountstatementList = $manager
            ->getRepository(AccountStatement::class)
            ->createQueryBuilder('e')
            ->where('e.title LIKE :title')
            ->setParameter('title', '%' . $search . '%')
            ->orWhere('e.enable LIKE :enable')
            ->setParameter('enable', '%' . $search . '%')
            ->orderBy('e.begin', 'DESC')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        $search = stripslashes($search);
        return $this->render(
            'account_statement/index.html.twig',
            [
                'accountstatementList' => $accountstatementList,
                'pages' => $pages,
                'page' => $page,
                'search' => $search,
                'searchForm' => $this->createSearchForm($search)->createView(),
            ]
        );
    }

    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_account_statement_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    #[Route(path: '/create/{account}', name: 'app_account_statement_create', methods: ['POST'])]
    public function create(Account $account, Request $request): RedirectResponse|Response
    {
        $accountstatement = new AccountStatement();
        $accountstatement->setAccount($account);
        $form = $this->createCreateForm($accountstatement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $accountstatement->setAuthor($this->getUser());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($accountstatement);
            $manager->flush();

            $this->addFlash('success', 'The AccountStatement has been created.');

            $url = $this->generateUrl('app_account_statement_show', [
                'id' => $accountstatement->getId(),
            ]);

            return $this->redirect($url);
        }
        return $this->render('account_statement/new.html.twig', [
            'accountstatement' => $accountstatement,
            'form' => $form->createView(),
        ]);
    }

    private function createCreateForm(AccountStatement $accountstatement): FormInterface
    {
        $form = $this->createForm(AccountStatementType::class, $accountstatement, [
            'action' => $this->generateUrl('app_account_statement_create', [
                'account' => $accountstatement->getAccount()->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    #[Route(path: '/new/{account}', name: 'app_account_statement_new', methods: ['GET'])]
    public function new(Account $account): Response
    {
        $accountstatement = new AccountStatement();
        $accountstatement->setAccount($account);
        $form = $this->createCreateForm($accountstatement);
        return $this->render('account_statement/new.html.twig', [
            'accountstatement' => $accountstatement,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/show/{id}', name: 'app_account_statement_show', methods: ['GET'])]
    public function show(AccountStatement $accountstatement, OperationRepository $operationRepository): Response
    {
        $stats = $operationRepository->getQueryStatsAccountStatement([$accountstatement->getId()])
            ->getQuery()
            ->getArrayResult();

        $stats = empty($stats) ? [
            'numberOperations' => 0,
            'sumCredit' => 0,
            'sumDebit' => 0,
        ] : reset($stats);
        $operations = $this->getManager()
            ->getRepository(Operation::class)
            ->findBy(
                ['accountStatement' => $accountstatement->getId()],
                ['date' => 'ASC']
            );
        return $this->render('account_statement/show.html.twig', [
            'accountstatement' => $accountstatement,
            'operations' => $operations,
            'statsOperations' => $stats,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'app_account_statement_edit', methods: ['GET'])]
    public function edit(AccountStatement $accountStatement): Response
    {
        $editForm = $this->createEditForm($accountStatement);
        return $this->render('account_statement/edit.html.twig', [
            'accountstatement' => $accountStatement,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Creates a form to edit a AccountStatement entity.
     *
     * @param AccountStatement $accountstatement The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(AccountStatement $accountstatement): FormInterface
    {
        $form = $this->createForm(AccountStatementType::class, $accountstatement, [
            'action' => $this->generateUrl('app_account_statement_update', [
                'id' => $accountstatement->getId(),
            ]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }
    /**
     * Edits an existing AccountStatement entity.
     *
     *
     * @return RedirectResponse|Response
     */
    #[Route(path: '/update/{id}', name: 'app_account_statement_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, AccountStatement $accountStatement): Response
    {
        $editForm = $this->createEditForm($accountStatement);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getManager()->flush();

            $this->addFlash('success', 'The AccountStatement has been updated.');

            return $this->redirect($this->generateUrl('app_account_statement_show', ['id' => $accountStatement->getId()]));
        }
        return $this->render('account_statement/edit.html.twig', [
            'accountstatement' => $accountStatement,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Deletes a AccountStatement entity.
     */
    #[Route(path: '/delete/{id}', name: 'app_account_statement_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, AccountStatement $accountStatement): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($accountStatement->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($accountStatement);
            $manager->flush();

            $this->addFlash('success', 'The AccountStatement has been deleted.');

            return $this->redirect($this->generateUrl('app_account_statement_index'));
        }
        return $this->render('account_statement/delete.html.twig', [
            'accountstatement' => $accountStatement,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
    /**
     * Creates a form to delete a AccountStatement entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_account_statement_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }
    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_account_statement_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();
        return $this->redirect($this->generateUrl('app_account_statement_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/add-document/{id}', name: 'app_account_statement_add_document', methods: ['POST'], options: ['expose' => 'true'])]
    public function addDocument(Request $request, AccountStatement $accountStatement): JsonResponse
    {
        $response = ResponseRequest::responseDefault();
        $manager = $this->getDoctrine()->getManager();
        $document = $this->getDocument($request->get('document'));
        $accountStatement->addDocument($document);
        $manager->persist($accountStatement);
        $manager->flush();
        $response->success = true;
        return new JsonResponse($response);
    }

    #[Route(
        path: '/operations-available/{id}',
        name: 'app_account_statement_operation_available',
        methods: ['GET', 'POST'],
        options: ['expose' => true]
    )]
    public function operationsAvailable(AccountStatement $accountStatement): JsonResponse
    {
        $response = ResponseRequest::responseDefault();
        $operations = $this->getManager()
            ->getRepository(Operation::class)
            ->getAvailableToAccountStatement($accountStatement);
        if (!empty($operations)) {
            foreach ($operations as $value) {
                $value['date'] = $value['date']->format('d/m/Y');
                $value['amount'] = number_format($value['amount'], 2, ',', ' ');

                $value['DT_RowId'] = $value['id'];

                $response->data[] = $value;
            }

            $response->success = true;
        }
        return new JsonResponse($response);
    }

    #[Route(
        path: '/add-operation/{id}',
        name: 'app_account_statement_add_operation',
        options: ['expose' => 'true'],
        methods: ['GET', 'POST']
    )]
    public function addOperations(Request $request, AccountStatement $accountStatement): JsonResponse
    {
        return $this->treatmentOperations($request->get('operations'), $accountStatement);
    }

    public function treatmentOperations(array $operationsId, AccountStatement $accountStatement = null): JsonResponse
    {
        $response = new Response();
        try {
            $response = ResponseRequest::responseDefault([
                'nbOperations' => count($operationsId),
                'nbOperationsFound' => 0,
            ]);

            $manager = $this->getManager();

            /** @var Operation[] $operations */
            $operations = $manager->getRepository(Operation::class)
                ->findBy(['id' => $operationsId]);

            $response->nbOperationsFound = count($operations);

            foreach ($operations as $operation) {
                $operation->setAccountStatement($accountStatement);
                $manager->persist($operation);
            }

            $manager->flush();

            $response->success = true;
        } catch (AppException $e) {
            $response->errors[] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    #[Route(
        path: '/delete-operation/{id}',
        name: 'app_account_statement_delete_operation',
        methods: ['GET', 'POST', 'DELETE']
    )]
    public function deleteOperations(Request $request): JsonResponse
    {
        return $this->treatmentOperations($request->get('operations'));
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\AccountSlip;
use App\Exception\AppException;
use App\Exception\EntityRepositoryNotFoundException;
use App\Exception\InvalidArgumentException;
use App\Fetcher\DocumentFetcher;
use App\Form\AccountSlipEditType;
use App\Form\AccountSlipType;
use App\Manager\SchoolManager;
use App\Manager\TransferManager;
use App\Model\ResponseModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/account-slip')]
class AccountSlipController extends AbstractBaseController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: '/list/{page}/{search}', name: 'app_account_slip_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = ''): Response
    {
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        // Get the total entries.
        $count = $this->getQuery($search)
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
        // Define the number of pages.
        $pages = ceil($count / 20);
        // Get the entries of current page.
        /** @var AccountSlip[] $accountSlipList */
        $accountSlipList = $this->getQuery($search)
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('account_slip/index.html.twig', [
            'accountslipList' => $accountSlipList,
            'count' => $count,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }

    private function getQuery(string $search): QueryBuilder
    {
        $manager = $this->getDoctrine()->getManager();

        return $manager
            ->getRepository(AccountSlip::class)
            ->createQueryBuilder('e')
            ->where('e.amount LIKE :amount')
            ->setParameter(':amount', '%'.$search.'%')
            ->orWhere('e.gender LIKE :gender')
            ->setParameter(':gender', '%'.$search.'%')
            ->orWhere('e.comment LIKE :comment')
            ->setParameter(':comment', '%'.$search.'%')
            ->orWhere('e.name LIKE :name')
            ->setParameter(':name', '%'.$search.'%')
            ->orWhere('e.reference LIKE :reference')
            ->setParameter(':reference', '%'.$search.'%')
            ->orWhere('e.operationCredit = :credit')
            ->setParameter(':credit', $search)
            ->orWhere('e.operationDebit = :debit')
            ->setParameter(':debit', $search)
            ->orderBy('e.date', 'DESC');
    }

    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_account_slip_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', SearchType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    #[Route(path: '/create', name: 'app_account_slip_create', methods: ['POST'])]
    public function create(Request $request, TransferManager $transferManager): Response
    {
        $accountSlip = new AccountSlip();
        $form = $this->createCreateForm($accountSlip);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $accountCredit = $form->get('accountCredit')->getData();
                $accountDebit = $form->get('accountDebit')->getData();
                $structure = $this->getEntitySchool()->getStructure();

                $accountSlip->setStructure($structure);

                $accountSlip = $transferManager
                    ->setAccountCredit($accountCredit)
                    ->setAccountDebit($accountDebit)
                    ->setAccountSlip($accountSlip)
                    ->createByForm();

                $this->addFlash('success', 'The AccountSlip has been created.');

                return $this->redirect($this->generateUrl('app_account_slip_show', ['id' => $accountSlip->getId()]));
            } catch (AppException|NonUniqueResultException $e) {
                $this->addFlash('danger', $this->trans('error.not_created', [], 'account_slip').$e->getMessage());
            }
        }

        return $this->render('account_slip/new.html.twig', [
            'accountslip' => $accountSlip,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a AccountSlip entity.
     *
     * @param AccountSlip $accountSlip The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(AccountSlip $accountSlip): FormInterface
    {
        $form = $this->createForm(AccountSlipType::class, $accountSlip, [
            'action' => $this->generateUrl('app_account_slip_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    #[Route(path: '/new', name: 'app_account_slip_new', methods: ['GET'])]
    public function new(): Response
    {
        $accountSlip = new AccountSlip();
        $form = $this->createCreateForm($accountSlip);

        return $this->render('account_slip/new.html.twig', [
            'accountslip' => $accountSlip,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/show/{id}', name: 'app_account_slip_show', methods: ['GET'])]
    public function show(AccountSlip $accountSlip): Response
    {
        return $this->render('account_slip/show.html.twig', [
            'accountslip' => $accountSlip,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'app_account_slip_edit', methods: ['GET'])]
    public function edit(AccountSlip $accountSlip): Response
    {
        $editForm = $this->createEditForm($accountSlip);

        return $this->render('account_slip/edit.html.twig', [
            'accountslip' => $accountSlip,
            'edit_form' => $editForm->createView(),
        ]);
    }

    private function createEditForm(AccountSlip $accountSlip): FormInterface
    {
        return $this->createForm(AccountSlipEditType::class, $accountSlip, [
            'action' => $this->generateUrl('app_account_slip_update', ['id' => $accountSlip->getId()]),
            'method' => Request::METHOD_PUT,
        ])
            ->add('submit', SubmitType::class, ['label' => 'Update']);
    }

    #[Route(path: '/update/{id}', name: 'app_account_slip_update', methods: ['PUT', 'POST'])]
    public function update(
        Request $request,
        AccountSlip $accountSlip,
        TransferManager $transferManager,
        SchoolManager $schoolManager,
    ): Response {
        $editForm = $this->createEditForm($accountSlip);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                if ($editForm->has('accountCredit')) {
                    $transferManager->setAccountCredit($editForm->get('accountCredit')->getData());
                }

                if ($editForm->has('accountDebit')) {
                    $transferManager->setAccountDebit($editForm->get('accountDebit')->getData());
                }

                if ($structure = $schoolManager->getEntitySchool()->getStructure()) {
                    $accountSlip->setStructure($structure);
                }

                $accountSlip = $transferManager
                    ->setAccountSlip($accountSlip)
                    ->update();

                $this->addFlash('success', 'The AccountSlip has been updated.');

                return $this->redirect($this->generateUrl('app_account_slip_show', ['id' => $accountSlip->getId()]));
            } catch (AppException $e) {
                $this->addFlash('danger', 'The AccountSlip has not updated : '.$e->getMessage());
            }
        }

        return $this->render('account_slip/edit.html.twig', [
            'accountslip' => $accountSlip,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a AccountSlip entity.
     *
     * @return RedirectResponse|Response
     */
    #[Route(path: '/delete/{id}', name: 'app_account_slip_delete', methods: ['GET', 'DELETE'])]
    public function delete(Request $request, AccountSlip $accountSlip): Response
    {
        $deleteForm = $this->createDeleteForm($accountSlip->getId());
        $deleteForm->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $manager->remove($accountSlip);
            $manager->flush();

            $this->addFlash('success', 'The AccountSlip has been deleted.');

            return $this->redirect($this->generateUrl('app_account_slip_index'));
        }

        return $this->render('account_slip/delete.html.twig', [
            'accountslip' => $accountSlip,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createDeleteForm($id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_account_slip_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, ['label' => $this->trans('Delete', [], 'account_slip')])
            ->getForm();
    }

    #[Route(path: '/search', name: 'app_account_slip_search', methods: ['POST'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_account_slip_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * @throws EntityRepositoryNotFoundException
     * @throws InvalidArgumentException
     */
    #[Route(path: '/set-document/{id}/{action}', name: 'app_account_slip_set_document', methods: ['POST'])]
    public function setDocument(
        Request $request,
        AccountSlip $accountSlip,
        string $action,
        DocumentFetcher $documentFetcher,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $document = $documentFetcher->getDocument($request->get('document'));

        switch ($action) {
            case 'add':
                $accountSlip->addDocument($document);
                $accountSlip->getOperationCredit()?->addDocument($document);
                $accountSlip->getOperationDebit()?->addDocument($document);

                break;
            case 'remove':
                $accountSlip->removeDocument($document);

                break;
            default:
                throw new InvalidDefinitionException('the action '.$action.' is not defined');
        }

        $entityManager->persist($accountSlip);
        $entityManager->flush();

        return $this->json(new ResponseModel(success: true));
    }
}

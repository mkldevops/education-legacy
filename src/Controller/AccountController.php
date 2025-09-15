<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Exception\AppException;
use App\Form\AccountOFXType;
use App\Form\AccountType;
use App\Manager\AccountManager;
use App\Manager\OFXManager;
use App\Manager\SchoolManager;
use App\Repository\AccountRepository;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ACCOUNTANT')]
class AccountController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    /**
     * @throws AppException
     */
    #[Route(path: '/account/{page}', name: 'app_account_index', methods: ['GET'])]
    public function index(AccountRepository $accountRepository, SchoolManager $schoolManager, int $page = 1): Response
    {
        $count = 20;
        $entities = $accountRepository
            ->getAccounts($schoolManager->getSchool(), false)
            ->getResult()
        ;

        return $this->render('account/index.html.twig', [
            'entities' => $entities,
            'pages' => ceil($count / 20),
            'page' => $page,
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/account/create', name: 'app_account_create')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager, SchoolManager $schoolManager): RedirectResponse|Response
    {
        $account = new Account();
        $form = $this->createCreateForm($account);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $account->setStructure($schoolManager->getEntitySchool()->getStructure());

            $entityManager->persist($account);
            $entityManager->flush();

            $this->addFlash('success', 'The Account has been created.');

            return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
        }

        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/account/new', name: 'app_account_new')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function new(): Response
    {
        $account = new Account();
        $form = $this->createCreateForm($account);

        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/account/show/{id}', name: 'app_account_show')]
    public function show(
        AccountManager $accountManager,
        #[MapEntity(id: 'id')] Account $account,
        SchoolManager $schoolManager,
        OperationRepository $operationRepository
    ): Response {
        if ($account->getStructure()?->getId() !== $schoolManager->getEntitySchool()->getStructure()?->getId()) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $data = ['account' => $account];
        $data['info'] = $operationRepository->getDataOperationsToAccount($account);
        // Check data to accountStatement
        $data += $accountManager->getDataAccountStatement($account);

        return $this->render('account/show.html.twig', $data);
    }

    #[Route(path: '/account/operations/{id}', name: 'app_account_operations')]
    public function operations(
        #[MapEntity(id: 'id')] Account $account
    ): Response {
        return $this->render('account/operations.html.twig', [
            'account' => $account,
        ]);
    }

    /**
     * Displays a form to edit an existing Account entity.
     */
    #[Route(path: '/account/edit/{id}', name: 'app_account_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function edit(
        #[MapEntity(id: 'id')] Account $account
    ): Response {
        $editForm = $this->createEditForm($account);

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/account/update/{id}', name: 'app_account_update')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(
        Request $request,
        #[MapEntity(id: 'id')] Account $account,
        EntityManagerInterface $entityManager
    ): Response {
        $editForm = $this->createEditForm($account);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Account has been updated.');

            return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
        }

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Account entity.
     */
    #[Route(path: '/account/delete/{id}', name: 'app_account_delete')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Account $account,
        EntityManagerInterface $entityManager
    ): RedirectResponse|Response {
        $deleteForm = $this->createDeleteForm($account->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($account);
            $entityManager->flush();

            $this->addFlash('success', 'The Account has been deleted.');

            return $this->redirectToRoute('app_account_index');
        }

        return $this->render('account/delete.html.twig', [
            'account' => $account,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @throws AppException
     * @throws \Exception
     */
    #[Route(path: '/account/ofx/{id}', name: 'app_account_ofx')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function ofx(
        Request $request,
        #[MapEntity(id: 'id')] Account $account,
        OFXManager $ofxManager
    ): Response {
        $logsOperations = [];
        $form = $this->createOFXForm($account)
            ->handleRequest($request)
        ;
        if ($form->isSubmitted() && $form->get('file')->isValid()) {
            $ofxManager->setAccount($account)
                ->setAccountTransfer($form->get('accountTransfer')->getData())
            ;
            $result = $ofxManager->ofx($form->get('file')->getData());
            $logsOperations = $ofxManager->getLogs();
            if ($result) {
                $this->addFlash('success', $this->translator->trans('account.ofx.treatment.ok', [], 'account'));
            } else {
                $this->addFlash('danger', $this->translator->trans('account.ofx.treatment.error', [], 'account'));
            }
        }

        return $this->render('account/ofx.html.twig', [
            'account' => $account,
            'logsOperations' => $logsOperations,
            'delete_form' => $form->createView(),
        ]);
    }

    private function createCreateForm(Account $account): FormInterface
    {
        $form = $this->createForm(AccountType::class, $account, [
            'action' => $this->generateUrl('app_account_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create']);

        return $form;
    }

    private function createEditForm(Account $account): FormInterface
    {
        $form = $this->createForm(AccountType::class, $account, [
            'action' => $this->generateUrl('app_account_update', ['id' => $account->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.update']);

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_account_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('form.button.delete', [], 'account'),
            ])
            ->getForm()
        ;
    }

    private function createOFXForm(Account $account): FormInterface
    {
        $form = $this->createForm(AccountOFXType::class, $account, [
            'action' => $this->generateUrl('app_account_ofx', [
                'id' => $account->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'account.ofx.form.launch']);

        return $form;
    }
}

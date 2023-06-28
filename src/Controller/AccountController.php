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
use App\Services\GoogleDriveService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_ACCOUNTANT")
 */
#[Route(path: '/account')]
class AccountController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/{page}', name: 'app_account_index', methods: ['GET'])]
    public function index(AccountRepository $repository, SchoolManager $schoolManager, int $page = 1): Response
    {
        $count = 20;
        $entities = $repository
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
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @throws AppException
     */
    #[Route(path: '/create', name: 'app_account_create')]
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

            return $this->redirect($this->generateUrl('app_account_show', ['id' => $account->getId()]));
        }

        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[Route(path: '/new', name: 'app_account_new')]
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
    #[Route(path: '/show/{id}', name: 'app_account_show')]
    public function show(AccountManager $accountManager, Account $account, SchoolManager $schoolManager, OperationRepository $operationRepository): Response
    {
        if ($account->getStructure()?->getId() !== $schoolManager->getEntitySchool()->getStructure()?->getId()) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $data = ['account' => $account];
        $data['info'] = $operationRepository->getDataOperationsToAccount($account);
        // Check data to accountStatement
        $data += $accountManager->getDataAccountStatement($account);

        return $this->render('account/show.html.twig', $data);
    }

    #[Route(path: '/operations/{id}', name: 'app_account_operations')]
    public function operations(Account $account): Response
    {
        return $this->render('account/operations.html.twig', [
            'account' => $account,
        ]);
    }

    /**
     * Displays a form to edit an existing Account entity.
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[Route(path: '/edit/{id}', name: 'app_account_edit')]
    public function edit(Account $account): Response
    {
        $editForm = $this->createEditForm($account);

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[Route(path: '/update/{id}', name: 'app_account_update')]
    public function update(Request $request, Account $account, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createEditForm($account);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Account has been updated.');

            return $this->redirect($this->generateUrl('app_account_show', ['id' => $account->getId()]));
        }

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Account entity.
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[Route(path: '/delete/{id}', name: 'app_account_delete')]
    public function delete(Request $request, Account $account, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($account->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($account);
            $entityManager->flush();

            $this->addFlash('success', 'The Account has been deleted.');

            return $this->redirect($this->generateUrl('app_account_index'));
        }

        return $this->render('account/delete.html.twig', [
            'account' => $account,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @throws AppException
     * @throws \Exception
     */
    #[Route(path: '/ofx/{id}', name: 'app_account_ofx')]
    public function ofx(Request $request, Account $account, OFXManager $manager, GoogleDriveService $googleDriveService): Response
    {
        $files = $googleDriveService
            ->getListFiles([
                'q' => "title contains 'ofx'",
                'spaces' => 'drive',
                'pageSize' => 20,
            ])
        ;
        $logsOperations = [];
        $form = $this->createOFXForm($account)
            ->handleRequest($request)
        ;
        if ($form->isSubmitted() && $form->get('file')->isValid()) {
            $manager->setAccount($account)
                ->setAccountTransfer($form->get('accountTransfer')->getData())
            ;
            $result = $manager->ofx($form->get('file')->getData());
            $logsOperations = $manager->getLogs();
            if ($result) {
                $this->addFlash('success', $this->translator->trans('account.ofx.treatment.ok', [], 'account'));
            } else {
                $this->addFlash('danger', $this->translator->trans('account.ofx.treatment.error', [], 'account'));
            }
        }

        return $this->render('account/ofx.html.twig', [
            'account' => $account,
            'files' => $files,
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

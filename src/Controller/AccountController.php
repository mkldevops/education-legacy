<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Account;
use App\Entity\Operation;
use App\Exception\AppException;
use App\Form\AccountOFXType;
use App\Form\AccountType;
use App\Manager\AccountManager;
use App\Manager\OFXManager;
use App\Repository\AccountRepository;
use App\Services\GoogleDriveService;
use Doctrine\ORM\ORMException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ACCOUNTANT")
 */
#[\Symfony\Component\Routing\Annotation\Route(path: '/account')]
class AccountController extends AbstractBaseController
{
    #[\Symfony\Component\Routing\Annotation\Route(path: '/{page}', name: 'app_account_index', methods: ['GET'])]
    public function index(AccountRepository $repository, int $page = 1) : Response
    {
        $count = 20;
        $entities = $repository
            ->getAccounts($this->getSchool(), false)
            ->getResult();
        return $this->render('account/index.html.twig', [
            'entities' => $entities,
            'pages' => ceil($count / 20),
            'page' => $page,
        ]);
    }
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/create', name: 'app_account_create')]
    public function create(Request $request) : RedirectResponse|Response
    {
        $account = new Account();
        $form = $this->createCreateForm($account);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $account->setStructure($this->getEntitySchool()->getStructure());

            $em->persist($account);
            $em->flush();

            $this->addFlash('success', 'The Account has been created.');

            return $this->redirect($this->generateUrl('app_account_show', ['id' => $account->getId()]));
        }
        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
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
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/new', name: 'app_account_new')]
    public function new() : Response
    {
        $account = new Account();
        $form = $this->createCreateForm($account);
        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Finds and displays a Account entity.
     *
     *
     * @throws ORMException
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/show/{id}', name: 'app_account_show')]
    public function show(AccountManager $accountManager, Account $account) : Response
    {
        if ($account->getStructure()->getId() !== $this->getSchool()->getStructure()->getId()) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }
        $data = ['account' => $account];
        $data['info'] = $this->getDoctrine()
            ->getManager()
            ->getRepository(Operation::class)
            ->getDataOperationsToAccount($account);
        // Check data to accountStatement
        $data += $accountManager->getDataAccountStatement($account);
        return $this->render('account/show.html.twig', $data);
    }
    /**
     * Finds and displays a Operations to Account entity.
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/operations/{id}', name: 'app_account_operations')]
    public function operations(Account $account) : \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('Account/operations.html.twig', [
            'account' => $account,
        ]);
    }
    /**
     * Displays a form to edit an existing Account entity.
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/edit/{id}', name: 'app_account_edit')]
    public function edit(Account $account) : Response
    {
        $editForm = $this->createEditForm($account);
        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
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
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @return RedirectResponse|Response
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/update/{id}', name: 'app_account_update')]
    public function update(Request $request, Account $account) : Response
    {
        $editForm = $this->createEditForm($account);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

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
    #[\Symfony\Component\Routing\Annotation\Route(path: '/delete/{id}', name: 'app_account_delete')]
    public function delete(Request $request, Account $account) : \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $deleteForm = $this->createDeleteForm($account->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();

            $this->addFlash('success', 'The Account has been deleted.');

            return $this->redirect($this->generateUrl('app_account_index'));
        }
        return $this->render('account/delete.html.twig', [
            'account' => $account,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_account_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('form.button.delete', [], 'account'),
            ])
            ->getForm();
    }
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @throws AppException
     * @throws Exception
     */
    #[\Symfony\Component\Routing\Annotation\Route(path: '/ofx/{id}', name: 'app_account_ofx')]
    public function ofx(Request            $request, Account            $account, OFXManager         $manager, GoogleDriveService $googleDriveService) : Response
    {
        $files = $googleDriveService
            ->getListFiles([
                'q' => "title contains 'ofx'",
                'spaces' => 'drive',
                'pageSize' => 20,
            ]);
        $logsOperations = [];
        $form = $this->createOFXForm($account)
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->get('file')->isValid()) {
            $manager->setAccount($account)
                ->setAccountTransfer($form->get('accountTransfer')->getData());
            $result = $manager->ofx($form->get('file')->getData());
            $logsOperations = $manager->getLogs();
            if ($result) {
                $this->addFlash('success', $this->trans('account.ofx.treatment.ok', [], 'account'));
            } else {
                $this->addFlash('danger', $this->trans('account.ofx.treatment.error', [], 'account'));
            }
        }
        return $this->render('account/ofx.html.twig', [
            'account' => $account,
            'files' => $files,
            'logsOperations' => $logsOperations,
            'delete_form' => $form->createView(),
        ]);
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

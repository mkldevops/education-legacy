<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Manager\DocumentManager;
use App\Model\ResponseModel;
use App\Services\ResponseRequest;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use ImagickException;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Document controller.
 */
#[Route(path: '/document')]
class DocumentController extends AbstractBaseController
{
    /**
     * Lists all Document entities.
     *
     *
     *
     * @throws NonUniqueResultException
     */
    #[Route(path: '/list/{page}/{search}', name: 'app_document_index', methods: ['GET'])]
    public function index(int $page = 1, string $search = ''): Response
    {
        $manager = $this->getDoctrine()->getManager();
        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');
        // Get the total entries.
        $count = $manager
            ->getRepository(Document::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.path LIKE :path')
            ->setParameter(':path', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
        // Define the number of pages.
        $pages = ceil($count / 20);
        // Get the entries of current page.
        $documentList = $manager
            ->getRepository(Document::class)
            ->createQueryBuilder('e')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.path LIKE :path')
            ->setParameter(':path', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        return $this->render('document/index.html.twig', [
            'documentList' => $documentList,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }
    /**
     * Creates a form to search Document entities.
     *
     *
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_document_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', SearchType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }
    /**
     * Creates a new Document entity.
     *
     *
     * @throws ImagickException
     */
    #[Route(path: '/create', name: 'app_document_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|Response
    {
        $document = new Document();
        $form = $this->createCreateForm($document);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $document->upload();

            $manager->persist($document);
            $manager->flush();

            $this->addFlash(
                'success',
                'The Document has been created.'
            );

            return $this->redirect($this->generateUrl('app_document_show', ['id' => $document->getId()]));
        }
        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Creates a form to create a Document entity.
     *
     * @param Document $document The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(Document $document): FormInterface
    {
        $form = $this->createForm(DocumentType::class, $document, [
            'action' => $this->generateUrl('app_document_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }
    /**
     * Displays a form to create a new Document entity.
     */
    #[Route(path: '/new', name: 'app_document_new', methods: ['GET'])]
    public function new(): Response
    {
        $document = new Document();
        $form = $this->createCreateForm($document);
        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Finds and displays a Document entity.
     */
    #[Route(path: '/show/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }
    /**
     * Displays a form to edit an existing Document entity.
     */
    #[Route(path: '/edit/{id}', name: 'app_document_edit', methods: ['GET'])]
    public function edit(Document $document): Response
    {
        $editForm = $this->createEditForm($document);
        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Creates a form to edit a Document entity.
     *
     * @param Document $document The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(Document $document): FormInterface
    {
        $form = $this->createForm(DocumentType::class, $document, [
            'action' => $this->generateUrl('app_document_update', ['id' => $document->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }
    /**
     * Edits an existing Document entity.
     */
    #[Route(path: '/update/{id}', name: 'app_document_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Document $document): RedirectResponse|Response
    {
        $manager = $this->getDoctrine()->getManager();
        $editForm = $this->createEditForm($document);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'The Document has been updated.');

            return $this->redirect($this->generateUrl('app_document_show', ['id' => $document->getId()]));
        }
        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }
    /**
     * Deletes a Document entity.
     */
    #[Route(path: '/delete/{id}', name: 'app_document_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Document $document, DocumentManager $documentManager): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($document->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $remove = $documentManager->removesWithLinks($document);

            if ($remove) {
                $this->addFlash('success', 'The Document has been deleted.');
            } else {
                $this->addFlash('danger', 'The Document has not deleted.');
            }

            return $this->redirect($this->generateUrl('app_document_index'));
        }
        return $this->render('document/delete.html.twig', [
            'document' => $document,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
    /**
     * Creates a form to delete a Document entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_document_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }
    /**
     * Redirect the the list URL with the search parameter.
     */
    #[Route(path: '/search', name: 'app_document_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();
        return $this->redirect($this->generateUrl('app_document_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
    public function getBaseUrl(Request $request): string
    {
        return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
    }
    #[Route(path: '/last', name: 'app_document_last', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function last(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $response = ResponseRequest::responseDefault();
        try {
            $response->data = $em->getRepository(Document::class)
                ->last($request->get('exists', [0]), $request->get('firstResult', 0));
        } catch (Exception $e) {
            $response->success = false;
            $response->errors[] = $e->getMessage();
        }
        return new JsonResponse($response);
    }
    /**
     * uploadAction.
     *
     *
     * @return Response|JsonResponse
     */
    #[Route(path: '/upload', name: 'app_document_upload', methods: ['POST'])]
    public function upload(Request $request, DocumentManager $documentManager): JsonResponse
    {
        $document = new Document();
        $response = ResponseModel::responseDefault(['upload' => null, 'document' => null]);
        try {
            $manager = $this->getManager();
            $school = $this->getEntitySchool();

            $document->setSchool($school)
                ->setAuthor($this->getUser())
                ->setEnable(true)
                ->setFile($request->files->get('file'))
                ->setName($request->get('name'))
                ->setPrefix($request->get('prefix'));

            $data = $documentManager->upload($document);
            $response->setData($data, 'upload');

            $manager->persist($document);
            $manager->flush();

            $data = $document->getInfos();
            $response->setData($data, 'document')
                ->setSuccess(true);
        } catch (Exception $e) {
            $documentManager->getLogger()->error($e->getMessage(), [
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            $response->setMessage($e->getMessage());
        }
        return ResponseRequest::jsonResponse($response);
    }
}

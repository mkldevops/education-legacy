<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Document;
use App\Exception\FileNotFoundException;
use App\Form\DocumentType;
use App\Manager\DocumentManager;
use App\Manager\SchoolManager;
use App\Repository\DocumentRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @throws FileNotFoundException
     */
    #[Route(path: '/document/create', name: 'app_document_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, DocumentManager $documentManager): RedirectResponse|Response
    {
        $document = new Document();
        $form = $this->createCreateForm($document);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $documentManager->upload($document);

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The Document has been created.'
            );

            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/document/new', name: 'app_document_new', methods: ['GET'])]
    public function new(): Response
    {
        $document = new Document();
        $form = $this->createCreateForm($document);

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/document/show/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route(path: '/document/edit/{id}', name: 'app_document_edit', methods: ['GET'])]
    public function edit(Document $document): Response
    {
        $editForm = $this->createEditForm($document);

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/document/update/{id}', name: 'app_document_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Document $document, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($document);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Document has been updated.');

            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/document/delete/{id}', name: 'app_document_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Document $document, DocumentManager $documentManager): Response
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

            return $this->redirect((string) $request->headers->get('referer'));
        }

        return $this->render('document/delete.html.twig', [
            'document' => $document,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    public function getBaseUrl(Request $request): string
    {
        return $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
    }

    /**
     * Get the last documents.
     */
    #[Route(path: '/document/last', name: 'app_document_last', options: ['expose' => true], methods: ['POST', 'GET'])]
    public function last(Request $request, DocumentRepository $documentRepository): JsonResponse
    {
        $responseModel = ResponseRequest::responseDefault();

        try {
            $responseModel->data = $documentRepository
                ->last($request->get('exists', [0]), $request->get('firstResult', 0))
            ;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'class' => $exception::class,
                'trace' => $exception->getTraceAsString(),
            ]);

            $responseModel->success = false;
            $responseModel->data = [];
            $responseModel->message = $exception->getMessage();

            return new JsonResponse($responseModel, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($responseModel);
    }

    /**
     * Upload a document.
     */
    #[Route(path: '/document/upload', name: 'app_document_upload', methods: ['POST'])]
    public function upload(Request $request, DocumentManager $documentManager, EntityManagerInterface $entityManager, SchoolManager $schoolManager): JsonResponse
    {
        try {
            $document = (new Document())
                ->setSchool($schoolManager->getEntitySchool())
                ->setEnable(true)
                ->setFile($request->files->get('file'))
                ->setName($request->get('name'))
                ->setPrefix($request->get('prefix'))
            ;

            $documentManager->upload($document);

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->json(['upload' => true, 'document' => $document->getInfos(), 'success' => true]);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'class' => $exception::class,
                'trace' => $exception->getTraceAsString(),
            ]);

            return $this->json([
                'upload' => false,
                'success' => false,
                'error' => $exception->getMessage(),
            ], 400);
        }
    }

    private function createCreateForm(Document $document): FormInterface
    {
        $form = $this->createForm(DocumentType::class, $document, [
            'action' => $this->generateUrl('app_document_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    private function createEditForm(Document $document): FormInterface
    {
        $form = $this->createForm(DocumentType::class, $document, [
            'action' => $this->generateUrl('app_document_update', ['id' => $document->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_document_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Document;
use App\Exception\AppException;
use App\Exception\FileNotFoundException;
use App\Form\DocumentType;
use App\Manager\DocumentManager;
use App\Manager\SchoolManager;
use App\Repository\DocumentRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/document')]
class DocumentController extends AbstractController
{
    /**
     * @throws FileNotFoundException
     */
    #[Route(path: '/create', name: 'app_document_create', methods: ['POST'])]
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

            return $this->redirect($this->generateUrl('app_document_show', ['id' => $document->getId()]));
        }

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

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

    #[Route(path: '/show/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'app_document_edit', methods: ['GET'])]
    public function edit(Document $document): Response
    {
        $editForm = $this->createEditForm($document);

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/update/{id}', name: 'app_document_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Document $document, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($document);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The Document has been updated.');

            return $this->redirect($this->generateUrl('app_document_show', ['id' => $document->getId()]));
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'edit_form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'app_document_delete', methods: ['DELETE', 'GET'])]
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
     * @throws AppException
     */
    #[Route(path: '/last', name: 'app_document_last', options: ['expose' => true], methods: ['POST', 'GET'])]
    public function last(Request $request, DocumentRepository $documentRepository): JsonResponse
    {
        $response = ResponseRequest::responseDefault();

        try {
            $response->data = $documentRepository
                ->last($request->get('exists', [0]), $request->get('firstResult', 0))
            ;
        } catch (Exception $e) {
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new JsonResponse($response);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/upload', name: 'app_document_upload', methods: ['POST'])]
    public function upload(Request $request, DocumentManager $documentManager, EntityManagerInterface $entityManager, SchoolManager $schoolManager): JsonResponse
    {
        try {
            $document = (new Document())
                ->setSchool($schoolManager->getEntitySchool())
                ->setAuthor($this->getUser())
                ->setEnable(true)
                ->setFile($request->files->get('file'))
                ->setName($request->get('name'))
                ->setPrefix($request->get('prefix'))
            ;

            $documentManager->upload($document);

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->json(['upload' => true, 'document' => $document->getInfos(), 'success' => true]);
        } catch (Exception $e) {
            $documentManager->getLogger()->error($e->getMessage(), [
                'class' => $e::class,
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
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

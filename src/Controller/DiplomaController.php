<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Diploma;
use App\Form\DiplomaType;
use App\Repository\DiplomaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/diploma')]
class DiplomaController extends AbstractController
{
    #[Route(path: '/', name: 'app_diploma_index', methods: ['GET'])]
    public function index(DiplomaRepository $diplomaRepository): Response
    {
        return $this->render('diploma/index.html.twig', [
            'diplomas' => $diplomaRepository->findAll(),
        ]);
    }
    #[Route(path: '/new', name: 'app_diploma_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $diploma = new Diploma();
        $form = $this->createForm(DiplomaType::class, $diploma);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($diploma);
            $entityManager->flush();

            return $this->redirectToRoute('app_diploma_index');
        }
        return $this->render('diploma/new.html.twig', [
            'diploma' => $diploma,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/{id}', name: 'app_diploma_show', methods: ['GET'])]
    public function show(Diploma $diploma): Response
    {
        return $this->render('diploma/show.html.twig', [
            'diploma' => $diploma,
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'app_diploma_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Diploma $diploma): Response
    {
        $form = $this->createForm(DiplomaType::class, $diploma);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_diploma_index', [
                'id' => $diploma->getId(),
            ]);
        }
        return $this->render('diploma/edit.html.twig', [
            'diploma' => $diploma,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/{id}', name: 'app_diploma_delete', methods: ['DELETE'])]
    public function delete(Request $request, Diploma $diploma): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $diploma->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($diploma);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_diploma_index');
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\GoogleService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GoogleController.
 *
 * @Route("/google")
 */
class GoogleController extends AbstractController
{
    /**
     * @Route("/auth", name="app_google_auth")
     *
     *
     * @throws Exception
     */
    public function index(Request $request, GoogleService $googleService): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createFormBuilder()
            ->add('code')
            ->add('submit', SubmitType::class)
            ->setMethod(Request::METHOD_POST)
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $googleService->setAuthCode($form->get('code')->getData())
                ->getClient();
            $this->addFlash('success', 'You are authentified');

            return $this->redirectToRoute('app_course_generate');
        }

        return $this->render('google/index.html.twig', [
            'form' => $form->createView(),
            'authUrl' => $googleService->getAuthUrl(),
        ]);
    }
}

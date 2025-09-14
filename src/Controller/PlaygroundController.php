<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaygroundController extends AbstractController
{
    #[Route('/playground/tw', name: 'app_playground_tailwind', methods: ['GET'])]
    public function tailwind(): Response
    {
        return $this->render('_playground/tailwind.html.twig', [
            'title' => 'Tailwind CSS Playground',
        ]);
    }
}

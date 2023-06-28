<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Meet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'api/meet')]
class MeetApiController extends AbstractController
{
    #[Route(path: '/update/{id}', options: ['expose' => 'true'], methods: ['POST'])]
    public function update(Request $request, Meet $meet, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $meet->setText($request->get('text'))
            ->setAuthor($security->getUser())
        ;

        $entityManager->persist($meet);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Meet updated successfully',
            'data' => ['id' => $meet->getId(), 'label' => $meet->__toString()],
        ]);
    }
}

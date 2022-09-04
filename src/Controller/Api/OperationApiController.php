<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Operation;
use App\Manager\OperationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/operation', options: ['expose' => 'true'])]
class OperationApiController extends AbstractController
{
    #[Route(path: '/update/{id}', name: 'app_api_operation_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Operation $operation, OperationManager $operationManager, SerializerInterface $serializer): JsonResponse
    {
        $operation = $serializer->deserialize($request->getContent(), Operation::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $operation,
        ]);

        $operationManager->update($operation);

        return $this->json([
            'success' => true,
            'message' => 'Operation updated successfully',
            'data' => OperationManager::getData($operation),
        ]);
    }
}

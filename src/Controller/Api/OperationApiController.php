<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Operation;
use App\Exception\AppException;
use App\Manager\OperationManager;
use App\Model\ResponseModel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/operation', options: ['expose' => 'true'])]
class OperationApiController extends AbstractBaseController
{
    #[Route(path: '/update/{id}', name: 'app_api_operation_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Operation $operation, OperationManager $operationManager) : JsonResponse
    {
        $result = new ResponseModel();
        try {
            $data = json_decode($request->getContent(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new AppException(json_last_error_msg());
            }

            $update = $operationManager->update($operation, $data);

            $result->setSuccess($update)
                ->setMessage('Operation updated successfully')
                ->setData(OperationManager::getData($operation));
        } catch (Exception $e) {
            $result->setMessage($e->getMessage());
        }
        return ResponseModel::jsonResponse($result);
    }
}

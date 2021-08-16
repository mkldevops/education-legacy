<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Person;
use App\Manager\PhoneManager;
use App\Model\ResponseModel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[\Symfony\Component\Routing\Annotation\Route(path: '/api/person', options: ['expose' => true])]
class PersonApiController extends AbstractBaseController
{
    #[\Symfony\Component\Routing\Annotation\Route(path: '/get-phones/{id}', name: 'app_api_person_get_phones', methods: ['GET'])]
    public function getPhones(Person $person) : JsonResponse
    {
        $response = ResponseModel::responseDefault();
        try {
            $response->setData([
                'phones' => PhoneManager::getAllPhones($person),
            ])->setSuccess(true);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage());
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }
        return ResponseModel::jsonResponse($response);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: '/update-phones/{id}', name: 'app_api_person_update_phone', methods: ['POST', 'PUT'])]
    public function updatePhone(Request $request, Person $person, PhoneManager $manager) : \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = ResponseModel::responseDefault();
        try {
            $key = $request->request->get('key', null);
            $value = $request->request->get('value', null);
            $result = $manager->updatePhone($person, $value, $key);
            $response->setSuccess($result);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }
        return ResponseModel::jsonResponse($response);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: '/delete-phones/{id}', name: 'app_api_person_delete_phone', methods: ['DELETE'])]
    public function deletePhone(Request $request, Person $person, PhoneManager $manager) : \Symfony\Component\HttpFoundation\JsonResponse
    {
        $response = ResponseModel::responseDefault();
        try {
            list('key' => $key) = $request->request->all();
            $result = $manager->deletePhone($person, $key);
            $response->setSuccess($result);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage());
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }
        return ResponseModel::jsonResponse($response);
    }
}

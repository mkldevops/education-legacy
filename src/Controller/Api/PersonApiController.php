<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Person;
use App\Exception\AppException;
use App\Manager\PhoneManager;
use App\Model\ResponseModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/person', options: ['expose' => true])]
class PersonApiController extends AbstractController
{
    #[Route(path: '/get-phones/{id}', name: 'app_api_person_get_phones', methods: ['GET'])]
    public function getPhones(Person $person): JsonResponse
    {
        return $this->json(new ResponseModel(success: true, data: ['phones' => PhoneManager::getAllPhones($person)]));
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/update-phones/{id}', name: 'app_api_person_update_phone', methods: ['POST', 'PUT'])]
    public function updatePhone(Request $request, Person $person, PhoneManager $manager): JsonResponse
    {
        $key = (string) $request->request->get('key', null);
        $value = (string) $request->request->get('value', null);
        $result = $manager->updatePhone($person, $value, $key);

        return $this->json(new ResponseModel(success: true, data: ['phones' => $result]));
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/delete-phones/{id}', name: 'app_api_person_delete_phone', methods: ['DELETE'])]
    public function deletePhone(Request $request, Person $person, PhoneManager $manager): JsonResponse
    {
        ['key' => $key] = $request->request->all();
        $result = $manager->deletePhone($person, $key);

        return $this->json(new ResponseModel(success: true, data: ['phones' => $result]));
    }
}

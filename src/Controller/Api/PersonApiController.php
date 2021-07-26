<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 26/12/18
 * Time: 17:00.
 */

namespace App\Controller\Api;

use App\Controller\Base\BaseController;
use App\Entity\Person;
use App\Manager\PhoneManager;
use App\Model\ResponseModel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of class PersonApiController.
 *
 * @Route("/api/person", options={"expose"=true})
 *
 * @author fahari
 */
class PersonApiController extends BaseController
{
    /**
     * @Route("/get-phones/{id}", name="app_api_person_get_phones", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getPhones(Person $person)
    {
        $response = ResponseModel::responseDefault();

        try {
            $response->setData([
                'phones' => PhoneManager::getAllPhones($person),
            ])->setSuccess(true);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage());
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }

        return ResponseModel::jsonResponse($response);
    }

    /**
     * @Route("/update-phones/{id}", name="app_api_person_update_phone", methods={"POST", "PUT"})
     *
     * @return JsonResponse
     */
    public function updatePhone(Request $request, Person $person, PhoneManager $manager)
    {
        $response = ResponseModel::responseDefault();

        try {
            $key = $request->request->get('key', null);
            $value = $request->request->get('value', null);
            $result = $manager->updatePhone($person, $value, $key);
            $response->setSuccess($result);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }

        return ResponseModel::jsonResponse($response);
    }

    /**
     * @Route("/delete-phones/{id}", name="app_api_person_delete_phone", methods={"DELETE"})
     *
     * @return JsonResponse
     */
    public function deletePhone(Request $request, Person $person, PhoneManager $manager)
    {
        $response = ResponseModel::responseDefault();

        try {
            list('key' => $key) = $request->request->all();
            $result = $manager->deletePhone($person, $key);
            $response->setSuccess($result);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__.' '.$e->getMessage());
            $response->setSuccess(false)
                ->setMessage($e->getMessage());
        }

        return ResponseModel::jsonResponse($response);
    }
}

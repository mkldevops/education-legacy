<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Meet;
use App\Model\ResponseModel;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperationApiController.
 */
#[\Symfony\Component\Routing\Annotation\Route(path: 'api/meet')]
class MeetApiController extends AbstractController
{
    #[\Symfony\Component\Routing\Annotation\Route(path: '/update/{id}', methods: ['POST'], options: ['expose' => 'true'])]
    public function update(Request $request, Meet $meet) : \Symfony\Component\HttpFoundation\JsonResponse
    {
        $result = new ResponseModel();
        try {
            $em = $this->getDoctrine()->getManager();
            $meet->setText($request->get('text'))
                ->setAuthor($this->getUser());

            $em->persist($meet);
            $em->flush();

            $result->setSuccess(true)
                ->setMessage('The family has been updated.')
                ->setData(['id' => $meet->getId(), 'label' => $meet->__toString()]);
        } catch (Exception $e) {
            $result->setMessage($e->getMessage())
                ->setSuccess(false);
        }
        return ResponseModel::jsonResponse($result);
    }
}

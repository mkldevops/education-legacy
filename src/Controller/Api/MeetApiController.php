<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Meet;
use App\Exception\AppException;
use App\Model\ResponseModel;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route(path: 'api/meet')]
class MeetApiController extends AbstractController
{
    /**
     * @throws AppException
     */
    #[Route(path: '/update/{id}', options: ['expose' => 'true'], methods: ['POST'])]
    public function update(Request $request, Meet $meet, Security $security): JsonResponse
    {
        $result = new ResponseModel();
        try {
            $em = $this->getDoctrine()->getManager();
            $meet->setText($request->get('text'))
                ->setAuthor($security->getUser());

            $em->persist($meet);
            $em->flush();

            $result->setSuccess(true)
                ->setMessage('The family has been updated.')
                ->setData(['id' => $meet->getId(), 'label' => $meet->__toString()]);
        } catch (Exception $e) {
            throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return ResponseModel::jsonResponse($result);
    }
}

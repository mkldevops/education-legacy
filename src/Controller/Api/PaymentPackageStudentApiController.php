<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\Period;
use App\Exception\AppException;
use App\Form\OperationPaymentStudentType;
use App\Manager\Interfaces\PaymentPackageStudentManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'api/payment-package-student')]
class PaymentPackageStudentApiController extends AbstractController
{
    /**
     * @throws AppException
     */
    #[Route(
        path: '/family/{family}/{period}',
        name: 'api_payment_package_student_family',
        options: ['expose' => 'true'],
        methods: ['POST']
    )]
    public function family(
        Request $request,
        Family $family,
        Period $period,
        PaymentPackageStudentManagerInterface $manager
    ): JsonResponse {
        $operation = new Operation();
        $this->createForm(OperationPaymentStudentType::class, $operation)
            ->handleRequest($request);

        $packages = $manager->familyPayments($operation, $family, $period);

        return $this->json($packages, context: [
            'ignore' => [],
            'circular_reference_handler' => fn ($object) => $object->__toString(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\Period;
use App\Form\OperationPaymentStudentType;
use App\Manager\Interfaces\PaymentPackageStudentManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentPackageStudentApiController extends AbstractController
{
    #[Route(
        path: 'api/payment-package-student/family/{family}/{period}',
        name: 'api_payment_package_student_family',
        options: ['expose' => 'true'],
        methods: [Request::METHOD_POST]
    )]
    public function family(
        Request $request,
        Family $family,
        Period $period,
        PaymentPackageStudentManagerInterface $paymentPackageStudentManager
    ): JsonResponse {
        $operation = new Operation();
        $this->createForm(OperationPaymentStudentType::class, $operation)
            ->handleRequest($request)
        ;

        $familyPaymentModel = $paymentPackageStudentManager->familyPayments($operation, $family, $period);

        return $this->json([
            'payments' => $familyPaymentModel->payments,
        ], context: [
            'ignore' => [],
            'circular_reference_handler' => static fn ($object) => $object->__toString(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\PaymentPackageStudent;
use App\Entity\Period;
use App\Form\OperationPaymentStudentType;
use App\Repository\PaymentPackageStudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('payment_package_student')]
final class PaymentPackageStudentComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Family $family = null;

    #[LiveProp]
    public ?Period $period = null;

    private ?FormView $formView = null;

    public function __construct(
        private readonly PaymentPackageStudentRepository $paymentPackageStudentRepository
    ) {}

    public function getForm(): FormView
    {
        if ($this->formView instanceof FormView) {
            return $this->formView;
        }

        $this->formView = $this->createForm(OperationPaymentStudentType::class, new Operation())
            ->createView()
        ;

        return $this->formView;
    }

    /**
     * @return array<int, array{operation: Operation, total: float, payments: PaymentPackageStudent[]}>
     */
    public function getPaymentsByOperation(): array
    {
        if (!$this->family instanceof Family || !$this->period instanceof Period) {
            return [];
        }

        $groupedPayments = [];

        /** @var PaymentPackageStudent $paymentPackageStudent */
        foreach ($this->paymentPackageStudentRepository->findForFamilyAndPeriod($this->family, $this->period) as $paymentPackageStudent) {
            $operation = $paymentPackageStudent->getOperation();
            $operationId = $operation->getId();

            if (null === $operationId) {
                continue;
            }

            if (!isset($groupedPayments[$operationId])) {
                $groupedPayments[$operationId] = [
                    'operation' => $operation,
                    'total' => 0.0,
                    'payments' => [],
                ];
            }

            $groupedPayments[$operationId]['payments'][] = $paymentPackageStudent;
            $groupedPayments[$operationId]['total'] += (float) $paymentPackageStudent->getAmount();
        }

        uasort($groupedPayments, static function (array $a, array $b): int {
            $aDate = $a['operation']->getDate() ?? $a['operation']->getDatePlanned();
            $bDate = $b['operation']->getDate() ?? $b['operation']->getDatePlanned();

            return ($bDate?->getTimestamp() ?? 0) <=> ($aDate?->getTimestamp() ?? 0);
        });

        return $groupedPayments;
    }
}

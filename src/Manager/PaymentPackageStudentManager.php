<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\PaymentPackageStudent;
use App\Entity\Period;
use App\Entity\TypeOperation;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Manager\Interfaces\PaymentPackageStudentManagerInterface;
use App\Model\FamilyPaymentModel;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\StudentRepository;
use App\Repository\TypeOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class PaymentPackageStudentManager implements PaymentPackageStudentManagerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private StudentRepository $studentRepository,
        private TypeOperationRepository $typeOperationRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    public function familyPayments(Operation $operation, Family $family, Period $period): FamilyPaymentModel
    {
        $students = $this->studentRepository->findByFamily($family);
        $packages = $this->packageStudentPeriodRepository->findBy(['period' => $period, 'student' => $students]);
        $familyPaymentModel = $this->persistPayments(family: $family, packages: $packages, operation: $operation);

        if (($user = $this->security->getUser()) !== null) {
            $operation->setPublisher($user);
        }
        if ($typeOperation = $this->typeOperationRepository->findOneBy([
            'code' => TypeOperation::TYPE_CODE_PAYMENT_PACKAGE_STUDENT,
        ])) {
            $operation->setTypeOperation($typeOperation);
        }
        $operation->setName(sprintf('[%s] %s', $period->__toString(), $family->__toString()));
        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        return $familyPaymentModel;
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    protected function persistPayments(Family $family, array $packages, Operation $operation): FamilyPaymentModel
    {
        $familyPaymentModel = new FamilyPaymentModel(operation: $operation, family: $family, packages: $packages);
        $amountRest = $familyPaymentModel->toPay % count($familyPaymentModel->packages);
        $amountByStudent = ($familyPaymentModel->toPay - $amountRest) / count($familyPaymentModel->packages);

        foreach ($familyPaymentModel->packages as $package) {
            if (($student = $package->getStudent()?->getId()) === null) {
                continue;
            }

            $amount  = $amountRest + $amountByStudent;
            $amountRest = 0;
            if ($package->getUnpaid() < $amount) {
                $amountRest += ($amount - $package->getUnpaid());
                $amount = $package->getUnpaid();
            }

            $this->logger->debug(__METHOD__, [
                'unpaid' => $package->getUnpaid(),
                'amount' => $amount,
                'amountRest' => $amountRest
            ]);

            $payment = (new PaymentPackageStudent())
                ->setAmount($amount)
                ->setOperation($familyPaymentModel->operation)
                ->setPackageStudentPeriod($package)
            ;
            $familyPaymentModel->payments[$student] = $payment->getAmount();
            $familyPaymentModel->toDue -= $payment->getAmount();
            $this->entityManager->persist($payment);
        }

        if ($amountRest > 0) {
            throw new AppException('Rest amount is not equal to zero');
        }

        return $familyPaymentModel;
    }
}

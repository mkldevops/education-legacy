<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Family;
use App\Entity\Operation;
use App\Entity\PaymentPackageStudent;
use App\Entity\Period;
use App\Entity\TypeOperation;
use App\Exception\InvalidArgumentException;
use App\Manager\Interfaces\PaymentPackageStudentManagerInterface;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\StudentRepository;
use App\Repository\TypeOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class PaymentPackageStudentManager implements PaymentPackageStudentManagerInterface
{
    public function __construct(
        private PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private StudentRepository $studentRepository,
        private TypeOperationRepository $typeOperationRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function familyPayments(Operation $operation, Family $family, Period $period): array
    {
        $students = $this->studentRepository->findByFamily($family);
        $packages = $this->packageStudentPeriodRepository->findBy(['period' => $period, 'student' => $students]);

        $toDue = $this->calculToDue($packages, ($toPay = $operation->getAmount()));
        $amountRest = $toPay % count($packages);
        $amountByStudent = ($toPay - $amountRest) / count($packages);

        $payments = [];
        foreach ($packages as $package) {
            if (($student = $package->getStudent()?->getId()) === null) {
                continue;
            }

            $payment = (new PaymentPackageStudent())
                ->setAmount((($amountRest-- > 0) ? 1 : 0) + $amountByStudent)
                ->setOperation($operation)
                ->setPackageStudentPeriod($package)
            ;
            $payments[$student] = $payment->getAmount();
            $toDue -= $payment->getAmount();
            $this->entityManager->persist($payment);
        }

        if (($user = $this->security->getUser()) !== null) {
            $operation->setPublisher($user);
        }
        if ($typeOperation = $this->typeOperationRepository->findOneBy([
            'code' => TypeOperation::TYPE_CODE_PAYMENT_PACKAGE_STUDENT,
        ])) {
            $operation->setTypeOperation($typeOperation);
        }
        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        return ['toDue' => $toDue, 'payments' => $payments];
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function calculToDue(array $packages, float $toPay): float
    {
        $toDue = 0.00;
        foreach ($packages as $package) {
            $toDue += $package->getUnpaid();
        }

        if ($toPay > $toDue) {
            throw new InvalidArgumentException(sprintf('Amount that you want to pay (%d €) is too high that you due amount (%d €)', $toPay, $toDue));
        }

        return $toDue;
    }
}

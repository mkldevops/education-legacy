<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Student;
use App\Entity\User;
use App\Exception\AppException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class StudentManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{students: mixed[], total: array{percentage: int|float, totalPreviousWithoutDiscount: int, discount: float|int, totalPreview: int, totalPaid: int, totalReminderPaid: int, type: array<int|string, int>&mixed[]}}
     */
    public function dataPaymentsStudents(array $students, Period $period): array
    {
        $list = self::getDataListDefault();

        foreach ($students as $data) {
            $student = $data['student'];
            $data['percentage'] = 0;
            $studentId = $student['id'];

            // if student is desactivated
            if (empty($data['student']['enable'])) {
                $data['amountTotal'] = (float) $data['paymentTotal'];
                $data['status'] = PackageStudentPeriod::STATUS_PAYMENT_SUCCESS;
                $data['percentage'] = 100;
            } else {
                if (!empty($data['amountTotal'])) {
                    $data['percentage'] = round((float) $data['paymentTotal'] / (float) $data['amountTotal'] * 100);
                }

                $data['status'] = PackageStudentPeriod::getStatusPaymentsStatic($data['percentage'], $period);
            }

            $list['students'][$studentId] = $data;
            ++$list['total']['type'][$data['status']];

            $list['total']['discount'] += isset($student['packagePeriods'][0]) ? $student['packagePeriods'][0]['discount'] : 0;
            $list['total']['totalPaid'] += (int) $data['paymentTotal'];
            $list['total']['totalPreview'] += (int) $data['amountTotal'];
        }

        if (!empty($list['total']['totalPreview'])) {
            $list['total']['percentage'] = $list['total']['totalPaid'] / $list['total']['totalPreview'] * 100;
        }

        $list['total']['totalReminderPaid'] = $list['total']['totalPreview'] - $list['total']['totalPaid'];

        return $list;
    }

    /**
     * @throws AppException
     */
    public function addPackage(Student $student, PackageStudentPeriod $packageStudentPeriod): Student
    {
        $this->logger->debug(__METHOD__, ['student' => $student, 'packageStudentPeriod' => $packageStudentPeriod]);
        $hasPackage = $this->entityManager
            ->getRepository(PackageStudentPeriod::class)
            ->findBy([
                'period' => $packageStudentPeriod->getPeriod(),
                'package' => $packageStudentPeriod->getPackage(),
                'student' => $packageStudentPeriod->getStudent(),
            ])
        ;

        if ([] !== $hasPackage) {
            throw new AppException('The package is already affected to student');
        }

        $packageStudentPeriod->setStudent($student);
        $packageStudentPeriod->setDateExpire($packageStudentPeriod->getPeriod()?->getEnd());
        $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()?->getPrice());

        if (($user = $this->security->getUser()) instanceof User) {
            $packageStudentPeriod->setAuthor($user);
        }

        $this->entityManager->persist($packageStudentPeriod);
        $this->entityManager->flush();

        return $student;
    }

    /**
     * @return array{students: never[], total: array{percentage: int, totalPreviousWithoutDiscount: int, discount: int, totalPreview: int, totalPaid: int, totalReminderPaid: int, type: array{danger: int, warning: int, info: int, success: int}}}
     */
    private static function getDataListDefault(): array
    {
        return [
            'students' => [],
            'total' => [
                'percentage' => 0,
                'totalPreviousWithoutDiscount' => 0,
                'discount' => 0,
                'totalPreview' => 0,
                'totalPaid' => 0,
                'totalReminderPaid' => 0,
                'type' => [
                    PackageStudentPeriod::STATUS_PAYMENT_DANGER => 0,
                    PackageStudentPeriod::STATUS_PAYMENT_WARNING => 0,
                    PackageStudentPeriod::STATUS_PAYMENT_INFO => 0,
                    PackageStudentPeriod::STATUS_PAYMENT_SUCCESS => 0,
                ],
            ],
        ];
    }
}

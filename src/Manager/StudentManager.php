<?php

declare(strict_types=1);


namespace App\Manager;

use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\Student;
use App\Exception\AppException;
use App\Model\ResponseModel;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class StudentManager
{

    public function __construct(
        private LoggerInterface $logger,
        private StudentRepository $repository,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return mixed[]
     */
    public function dataPayementsStudents(array $students, Period $period) : array
    {
        $list = self::getDataListDefault();

        foreach ($students as $data) {
            $student = $data['student'];
            $data['percentage'] = 0;
            $studentId = $student['id'];

            // if student is desactivated
            if (empty($data['student']['enable'])) {
                $data['amountTotal'] = (float)$data['paymentTotal'];
                $data['status'] = PackageStudentPeriod::STATUS_PAYMENT_SUCCESS;
                $data['percentage'] = 100;
            } else {
                if (!empty($data['amountTotal'])) {
                    $data['percentage'] = round((float)$data['paymentTotal'] / (float)$data['amountTotal'] * 100);
                }

                $data['status'] = PackageStudentPeriod::getStatusPaymentsStatic($data['percentage'], $period);
            }

            $list['students'][$studentId] = $data;
            ++$list['total']['type'][$data['status']];

            $list['total']['discount'] += isset($student['packagePeriods'][0]) ? $student['packagePeriods'][0]['discount'] : 0;
            $list['total']['totalPaid'] += (int)$data['paymentTotal'];
            $list['total']['totalPreview'] += (int)$data['amountTotal'];
        }

        if (!empty($list['total']['totalPreview'])) {
            $list['total']['percentage'] = $list['total']['totalPaid'] / $list['total']['totalPreview'] * 100;
        }

        $list['total']['totalReminderPaid'] = $list['total']['totalPreview'] - $list['total']['totalPaid'];

        return $list;
    }

    /**
     * @return array<string, mixed[]>
     */
    private static function getDataListDefault() : array
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

    public function synchronize(int $limit): ResponseModel
    {
        $response = new ResponseModel();

        $students = $this->repository->findBy(['person' => null]);

        if (empty($students)) {
            return $response->setSuccess(true)
                ->setMessage('Nothing data to treat');
        }

        foreach ($students as $student) {
            $person = (new Person())
                ->setAuthor($student->getAuthor())
                ->setEnable($student->getEnable())
                ->setAddress($student->getAddress())
                ->setBirthday($student->getBirthday())
                ->setBirthplace($student->getBirthplace())
                ->setCity($student->getTown())
                ->setEmail($student->getEmail())
                ->setForname($student->getForname())
                ->setName($student->getName())
                ->setGender($student->getGender())
                ->setImage($student->getImage())
                ->setPhone($student->getPhone())
                ->setZip($student->getPostcode())
                ->setCreatedAt($student->getCreatedAt());

            $student->setPerson($person);
            $this->entityManager->persist($person);
            $this->entityManager->persist($student);
        }
        $this->entityManager->flush();

        return $response->setSuccess(true)
            ->setMessage('The synchronization had successfully');
    }

    /**
     * @throws Exception
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
            ]);

        if (!empty($hasPackage)) {
            throw new AppException('The package is already affected to student');
        }

        $packageStudentPeriod->setStudent($student);
        $packageStudentPeriod->setDateExpire($packageStudentPeriod->getPeriod()?->getEnd());
        $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()?->getPrice());

        if (($user = $this->security->getUser()) !== null) {
            $packageStudentPeriod->setAuthor($user);
        }

        $this->entityManager->persist($packageStudentPeriod);
        $this->entityManager->flush();

        return $student;
    }
}

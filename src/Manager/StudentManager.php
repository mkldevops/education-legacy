<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 04/06/2016
 * Time: 20:37.
 */

namespace App\Manager;

use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\Student;
use App\Model\ResponseModel;
use App\Services\AbstractFullService;
use Exception;

/**
 * Class StudentManager.
 */
class StudentManager extends AbstractFullService
{
    /**
     * Organize the type pament to package student.
     *
     * @return array
     */
    public function dataPayementsStudents(array $students, Period $period)
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
     * Get Data List Default.
     *
     * @return array
     */
    private static function getDataListDefault()
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

        $students = $this->getEntityManager()
            ->getRepository(Student::class)
            ->findBy(['person' => null]);

        if (empty($students)) {
            return $response->setSuccess(true)
                ->setMessage('Nothing data to treat');
        }

        foreach ($students as $student) {
            $person = (new Person())
                ->setAuthor($student->getAuthor())
                ->setEnable((bool)$student->getStatus())
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
            $this->getEntityManager()->persist($person);
            $this->getEntityManager()->persist($student);
            $this->getEntityManager()->flush();
        }

        return $response->setSuccess(true)
            ->setMessage('The synchronization had successfully');
    }

    /**
     * @throws Exception
     */
    public function addPackage(Student $student, PackageStudentPeriod $packageStudentPeriod): Student
    {
        $this->logger->debug(__METHOD__, ['student' => $student, 'packageStudentPeriod' => $packageStudentPeriod]);
        $hasPackage = $this->getEntityManager()
            ->getRepository(PackageStudentPeriod::class)
            ->findBy([
                'period' => $packageStudentPeriod->getPeriod(),
                'package' => $packageStudentPeriod->getPackage(),
                'student' => $packageStudentPeriod->getStudent(),
            ]);

        if (!empty($hasPackage)) {
            throw new Exception('The package is already affected to student');
        }

        $manager = $this->getEntityManager();

        $packageStudentPeriod->setStudent($student);
        $packageStudentPeriod->setDateExpire($packageStudentPeriod->getPeriod()->getEnd());
        $packageStudentPeriod->setAmount($packageStudentPeriod->getPackage()->getPrice());

        $oUser = $this->getUser();
        $packageStudentPeriod->setAuthor($oUser);

        $manager->persist($packageStudentPeriod);
        $manager->flush();

        return $student;
    }
}

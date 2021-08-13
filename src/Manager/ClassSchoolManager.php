<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 13/12/18
 * Time: 21:48.
 */

namespace App\Manager;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Entity\Student;
use App\Services\AbstractFullService;
use DateTime;
use Exception;

/**
 * Description of class ClassSchoolManager.
 *
 * @author  fahari
 */
class ClassSchoolManager extends AbstractFullService
{
    /**
     * @return bool
     *
     * @throws Exception
     */
    public function addStudentToClass(array $student, ClassPeriod $classPeriod)
    {
        if (!empty($students) && is_array($students)) {
            $manager = $this->getEntityManager();

            $students = $manager->getRepository(Student::class)
                ->findBy(['id' => array_keys($students)]);

            foreach ($students as $student) {
                $classPeriodStudent = new ClassPeriodStudent();

                $oBegin = new DateTime();

                // On verifie si la date debut de la periode n'est pas encore passé
                if ($classPeriod->getPeriod()->getBegin()->getTimestamp() > time()) {
                    $oBegin = $classPeriod->getPeriod()->getBegin();
                }

                $classPeriodStudent->setClassPeriod($classPeriod);
                $classPeriodStudent->setBegin($oBegin);
                $classPeriodStudent->setEnd($classPeriod->getPeriod()->getEnd());
                $classPeriodStudent->setStudent($student);
                $classPeriodStudent->setAuthor($this->getUser());

                $manager->persist($classPeriodStudent);
            }

            $manager->flush();

            return true;
        }

        return false;
    }
}

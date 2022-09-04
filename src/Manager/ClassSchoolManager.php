<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Repository\StudentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ClassSchoolManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StudentRepository $studentRepository,
        private Security $security,
    ) {
    }

    public function addStudentToClass(array $students, ClassPeriod $classPeriod): bool
    {
        if (empty($students)) {
            return false;
        }

        $students = $this->studentRepository->findBy(['id' => array_keys($students)]);

        $begin = new DateTime();
        if ($classPeriod->getPeriod()->getBegin()?->getTimestamp() > time()) {
            $begin = $classPeriod->getPeriod()->getBegin();
        }

        foreach ($students as $student) {
            $classPeriodStudent = (new ClassPeriodStudent())
                ->setClassPeriod($classPeriod)
                ->setBegin($begin)
                ->setEnd($classPeriod->getPeriod()->getEnd())
                ->setStudent($student)
                ->setAuthor($this->security->getUser())
            ;

            $this->entityManager->persist($classPeriodStudent);
        }

        $this->entityManager->flush();

        return true;
    }
}

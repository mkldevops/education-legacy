<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClassSchoolManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StudentRepository $studentRepository
    ) {}

    public function addStudentToClass(array $students, ClassPeriod $classPeriod): bool
    {
        if ([] === $students) {
            return false;
        }

        $students = $this->studentRepository->findBy(['id' => array_keys($students)]);

        $begin = new \DateTime();
        if ($classPeriod->getPeriod()->getBegin()?->getTimestamp() > time()) {
            $begin = $classPeriod->getPeriod()->getBegin();
        }

        foreach ($students as $student) {
            $classPeriodStudent = (new ClassPeriodStudent())
                ->setClassPeriod($classPeriod)
                ->setBegin($begin)
                ->setEnd($classPeriod->getPeriod()->getEnd())
                ->setStudent($student)
            ;

            $this->entityManager->persist($classPeriodStudent);
        }

        $this->entityManager->flush();

        return true;
    }
}

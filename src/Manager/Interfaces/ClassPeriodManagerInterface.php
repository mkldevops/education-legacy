<?php

namespace App\Manager\Interfaces;

use App\Entity\ClassPeriod;
use App\Entity\Period;
use App\Entity\School;

interface ClassPeriodManagerInterface
{
    public function findClassPeriod(string $name, Period $period, School $school): ?ClassPeriod;
    public function getPackageStudent(ClassPeriod $classPeriod): array;
    public function getStudentsInClassPeriod(ClassPeriod $classPeriod): array;
}
<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 04/06/2016
 * Time: 20:37.
 */

namespace App\Manager;

use App\Entity\Family;
use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Person;
use App\Services\AbstractFullService;

/**
 * Class StudentManager.
 */
class FamilyManager extends AbstractFullService
{
    /**
     * @return Person[]
     */
    public function getPersons(Family $family, Period $period): array
    {
        return $this->getEntityManager()
            ->getRepository(Person::class)
            ->getPersonsToFamily($family, $period);
    }

    /**
     * @var Person[]
     */
    public function getPackages(array $persons, Period $period): void
    {
        $students = array_map(fn($person) => $person->getStudent() ?? null, $persons);

        $packages = $this->entityManager->getRepository(PackageStudentPeriod::class)
            ->findBy(['student' => $students, 'period' => $period]);

        dd($packages);
    }
}

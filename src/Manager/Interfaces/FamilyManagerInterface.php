<?php

declare(strict_types=1);

namespace App\Manager\Interfaces;

use App\Entity\Family;
use App\Entity\Period;
use App\Exception\AppException;
use Symfony\Component\Form\FormInterface;

interface FamilyManagerInterface
{
    public function getPersons(Family $family, Period $period): array;

    public function getPackages(array $persons, Period $period): array;

    /**
     * @throws AppException
     */
    public function persistData(Family $family, FormInterface $form): bool;
}

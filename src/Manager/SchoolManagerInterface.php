<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\School;

interface SchoolManagerInterface
{
    public function getEntitySchool(): School;

    public function getEntitySchoolOnSession(): School;

    public function getSchool(): School;

    public function getSchoolOnSession(): School;
}

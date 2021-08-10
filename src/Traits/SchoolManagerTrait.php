<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\School;
use App\Exception\SchoolException;
use App\Manager\SchoolManager;

trait SchoolManagerTrait
{
    protected SchoolManager $schoolManager;

    /**
     * @required
     */
    public function setSchoolManager(SchoolManager $schoolManager): void
    {
        $this->schoolManager = $schoolManager;
    }

    /**
     * @throws SchoolException
     */
    public function findSchool(int $id): School
    {
        return $this->schoolManager->findSchool($id);
    }
}

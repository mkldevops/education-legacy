<?php

declare(strict_types=1);

namespace App\Traits\Accessor;

use App\Entity\School;

trait SchoolAccessorTrait
{
    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }
}

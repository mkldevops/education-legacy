<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\School;
use Doctrine\ORM\Mapping as ORM;

trait SchoolEntityTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=School::class, cascade={"persist"})
     */
    protected ?School $school = null;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(School $school): self
    {
        $this->school = $school;

        return $this;
    }
}

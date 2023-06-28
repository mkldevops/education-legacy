<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\School;
use App\Traits\Accessor\SchoolAccessorTrait;
use Doctrine\ORM\Mapping as ORM;

trait SchoolEntityTrait
{
    use SchoolAccessorTrait;

    #[ORM\ManyToOne(targetEntity: School::class, cascade: ['persist'])]
    protected ?School $school = null;
}

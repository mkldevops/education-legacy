<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

trait BaseEntityTrait
{
    use NameEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntityTrait;
    use SoftDeleteableEntity;
}

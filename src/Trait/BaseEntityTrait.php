<?php

declare(strict_types=1);

namespace App\Trait;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

trait BaseEntityTrait
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;
}

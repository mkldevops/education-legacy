<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\School;

class SchoolList
{
    public const SCHOOL_SELECTED = 'selected';
    public const SCHOOL_LIST = 'list';

    public function __construct(public ?array $list, public ?\App\Entity\School $selected)
    {
    }
}

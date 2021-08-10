<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\School;

class SchoolList
{
    public const SCHOOL_SELECTED = 'selected';
    public const SCHOOL_LIST = 'list';

    public ?School $selected = null;
    public ?array $list = [];

    public function __construct(?array $list, ?School $selected)
    {
        $this->list = $list;
        $this->selected = $selected;
    }
}

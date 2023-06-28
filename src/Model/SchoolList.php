<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\School;

class SchoolList
{
    /**
     * @var string
     */
    final public const SCHOOL_SELECTED = 'selected';

    /**
     * @var string
     */
    final public const SCHOOL_LIST = 'list';

    public function __construct(public ?array $list, public ?School $selected)
    {
    }
}

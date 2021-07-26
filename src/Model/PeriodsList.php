<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Period;

class PeriodsList
{
    public const PERIOD_SELECTED = 'selected';
    public const PERIOD_CURRENT = 'current';
    public const PERIOD_LIST = 'list';

    public ?Period $current = null;
    public ?Period $selected = null;
    public ?array $list = [];

    public function __construct(array $list, Period $selected, Period $current = null)
    {
        $this->list = $list;
        $this->selected = $selected;
        $this->current = $current ?? $selected;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Period;

class PeriodsList
{
    /**
     * @var string
     */
    public const PERIOD_SELECTED = 'selected';

    /**
     * @var string
     */
    public const PERIOD_CURRENT = 'current';

    /**
     * @var string
     */
    public const PERIOD_LIST = 'list';

    public ?Period $current = null;

    public ?Period $selected = null;

    public function __construct(public ?array $list, Period $selected, Period $current = null)
    {
        $this->selected = $selected;
        $this->current = $current ?? $selected;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Period;

class PeriodsList
{
    /**
     * @var string
     */
    final public const PERIOD_SELECTED = 'selected';

    /**
     * @var string
     */
    final public const PERIOD_CURRENT = 'current';

    /**
     * @var string
     */
    final public const PERIOD_LIST = 'list';

    public ?Period $current = null;

    public function __construct(public ?array $list, public ?\App\Entity\Period $selected, Period $current = null)
    {
        $this->current = $current ?? $selected;
    }
}

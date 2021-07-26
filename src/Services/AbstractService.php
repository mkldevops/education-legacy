<?php

declare(strict_types=1);

namespace App\Services;

use Fardus\Traits\Symfony\Manager\LoggerTrait;
use Fardus\Traits\Symfony\Manager\TranslatorTrait;

abstract class AbstractService
{
    use LoggerTrait;
    use TranslatorTrait;
}

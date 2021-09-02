<?php

declare(strict_types=1);

namespace App\Exception;

class EntityRepositoryNotFoundException extends AppException
{
    protected $code = 404;
}

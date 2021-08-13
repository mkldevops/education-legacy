<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class AppException extends Exception
{

    public function __toString(): string
    {
        return $this->getMessage();
    }
}

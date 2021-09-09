<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FileNotFoundException extends AppException
{
    public function __construct(
        string $message = '',
        int $code = Response::HTTP_NOT_FOUND,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

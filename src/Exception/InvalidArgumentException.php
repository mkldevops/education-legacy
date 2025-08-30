<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidArgumentException extends AppException
{
    public function __construct(
        string $message = '',
        int $code = Response::HTTP_BAD_REQUEST,
        ?\Throwable $throwable = null
    ) {
        parent::__construct($message, $code, $throwable);
    }
}

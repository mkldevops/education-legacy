<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class AppException extends \Exception implements MainErrorInterface, \Stringable
{
    public function __construct(
        string $message = '',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?\Throwable $throwable = null
    ) {
        parent::__construct($message, $code, $throwable);
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }
}

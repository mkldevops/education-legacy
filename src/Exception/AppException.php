<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class AppException extends \Exception implements MainErrorInterface
{
    public function __construct(
        string $message = '',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }
}

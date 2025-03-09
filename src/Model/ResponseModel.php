<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseModel
{
    public function __construct(
        public bool $success = false,
        public string $message = '',
        public array $data = [],
        public ?array $errors = null,
    ) {}

    public static function responseDefault(array $data = []): self
    {
        return new self(data: $data);
    }

    public static function jsonResponse(self $response): JsonResponse
    {
        $statusCode = Response::HTTP_OK;

        if (!$response->isSuccess()) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse($response->getResult(), $statusCode);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return array{success: bool, data: mixed[], message: string}
     */
    public function getResult(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}

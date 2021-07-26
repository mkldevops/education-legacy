<?php

declare(strict_types=1);

namespace App\Model;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseModel
{
    protected bool $success;
    protected string $message;
    protected array $data = [];

    public static function responseDefault(array $data = []): object
    {
        $response = new static();
        $response->setData($data);

        return $response;
    }

    public static function jsonResponse(ResponseModel $response): JsonResponse
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

    #[ArrayShape(['success' => 'bool', 'data' => 'array|null', 'message' => 'null|string'])]
    public function getResult(): array
    {
        return [
            'success' => $this->isSuccess(),
            'data' => $this->getData(),
            'message' => $this->getMessage(),
        ];
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}

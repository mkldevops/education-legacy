<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\AppException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 100)]
readonly class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private string $env
    ) {}

    public function __invoke(ExceptionEvent $exceptionEvent): void
    {
        $throwable = $exceptionEvent->getThrowable();
        $request = $exceptionEvent->getRequest();

        // Only handle JSON requests
        if (!$this->isJsonRequest($request)) {
            return;
        }

        // Log the exception
        $this->logger->error('Exception caught by ExceptionListener', [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
        ]);

        // Create JSON response
        $jsonResponse = $this->createJsonErrorResponse($throwable);
        $exceptionEvent->setResponse($jsonResponse);
    }

    private function isJsonRequest(Request $request): bool
    {
        // Check Accept header
        if ('application/json' === $request->headers->get('Accept')) {
            return true;
        }

        // Check Content-Type header
        if (str_contains((string) $request->headers->get('Content-Type', ''), 'application/json')) {
            return true;
        }

        // Check if request is AJAX with JSON expectation
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        // Check if route name suggests API endpoint
        $route = $request->attributes->get('_route', '');

        return str_contains((string) $route, 'api_') || str_contains((string) $route, '_api_');
    }

    private function createJsonErrorResponse(\Throwable $throwable): JsonResponse
    {
        $statusCode = $this->getStatusCode($throwable);
        $data = [
            'success' => false,
            'error' => [
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'type' => $throwable::class,
            ],
        ];

        // Add debug information in development environment
        if ('dev' === $this->env) {
            $data['debug'] = [
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
                'previous' => $throwable->getPrevious() instanceof \Throwable ? [
                    'message' => $throwable->getPrevious()->getMessage(),
                    'file' => $throwable->getPrevious()->getFile(),
                    'line' => $throwable->getPrevious()->getLine(),
                ] : null,
            ];
        }

        return new JsonResponse($data, $statusCode);
    }

    private function getStatusCode(\Throwable $throwable): int
    {
        // Handle HTTP exceptions (includes NotFoundHttpException, AccessDeniedHttpException, etc.)
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getStatusCode();
        }

        // Handle application-specific exceptions
        if ($throwable instanceof AppException) {
            return Response::HTTP_BAD_REQUEST;
        }

        // Default to internal server error
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}

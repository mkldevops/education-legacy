<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $env,
        private bool $debug
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    public function processException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logger->error(__METHOD__, compact('exception'));
        if ('dev' === $this->env && !str_contains($event->getRequest()->getPathInfo(), '/api')) {
            throw $exception;
        }

        $data = ['error' => sprintf('Error : %s', $exception->getMessage())];
        if ($this->debug) {
            $data['traces'] = $exception->getTrace();
        }

        $response = new JsonResponse();
        $response->setData($data);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->add($exception->getHeaders());
        } else {
            $response->setStatusCode($exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}

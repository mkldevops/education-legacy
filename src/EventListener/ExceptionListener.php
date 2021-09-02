<?php

declare(strict_types=1);

namespace App\EventListener;

use Fardus\Traits\Symfony\Controller\ResponseTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionListener
{
    public function __construct(private LoggerInterface $logger, private bool $debug)
    {
    }

    /**
     * @throws Throwable
     */
    public function onKernelException(ExceptionEvent $event): void
    {

        $exception = $event->getThrowable();
        $this->logger->error(__METHOD__, compact('exception'));
        if (!str_contains($event->getRequest()->getPathInfo(), '/api')) {
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

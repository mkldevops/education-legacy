<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly string $env,
        private readonly bool $debug
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
     * @throws \Throwable
     */
    public function processException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logger->error(__METHOD__, ['exception' => $exception]);

        if ($exception instanceof AccessDeniedException && null === $this->security->getUser()) {
            $event->setResponse(new RedirectResponse('/login'));

            return;
        }

        if ('dev' === $this->env && !str_contains($event->getRequest()->getPathInfo(), '/api')) {
            throw $exception;
        }

        $data = ['error' => sprintf('Error : %s', $exception->getMessage())];
        if ($this->debug) {
            $data['traces'] = $exception->getTrace();
        }

        $response = new JsonResponse($data, $exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->add($exception->getHeaders());
        }

        $event->setResponse($response);
    }
}

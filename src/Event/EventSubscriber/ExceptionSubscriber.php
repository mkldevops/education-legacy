<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

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
use Symfony\Component\Security\Core\User\UserInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly string $env,
        private readonly bool $debug
    ) {}

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
    public function processException(ExceptionEvent $exceptionEvent): void
    {
        $throwable = $exceptionEvent->getThrowable();

        // Don't log AccessDeniedException as errors since they're expected security behavior
        if (!$throwable instanceof AccessDeniedException) {
            $this->logger->error(__METHOD__, ['exception' => $throwable]);
        }

        if ($throwable instanceof AccessDeniedException && !$this->security->getUser() instanceof UserInterface) {
            $exceptionEvent->setResponse(new RedirectResponse('/login'));

            return;
        }

        if ('dev' === $this->env && !str_contains($exceptionEvent->getRequest()->getPathInfo(), '/api')) {
            throw $throwable;
        }

        $data = ['error' => \sprintf('Error : %s', $throwable->getMessage())];
        if ($this->debug) {
            $data['traces'] = $throwable->getTrace();
        }

        // Determine a valid HTTP status code.
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
        } else {
            $code = (int) $throwable->getCode();
            if ($code >= 400 && $code <= 599) {
                $status = $code;
            }
        }

        $jsonResponse = new JsonResponse($data, $status);

        if ($throwable instanceof HttpExceptionInterface) {
            $jsonResponse->headers->add($throwable->getHeaders());
        }

        $exceptionEvent->setResponse($jsonResponse);
    }
}

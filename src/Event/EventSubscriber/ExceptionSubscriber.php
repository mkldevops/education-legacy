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

        // Don't log certain exceptions as errors since they're expected behavior
        if (!$throwable instanceof AccessDeniedException
            && !str_contains($throwable->getMessage(), 'You cannot refresh a user from the EntityUserProvider')) {
            $this->logger->error(__METHOD__, ['exception' => $throwable]);
        }

        // Handle EntityUserProvider refresh errors (common in tests)
        if ($throwable instanceof \InvalidArgumentException
            && str_contains($throwable->getMessage(), 'You cannot refresh a user from the EntityUserProvider')) {
            $exceptionEvent->setResponse(new RedirectResponse('/login'));

            return;
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
            $traces = [];
            foreach ($throwable->getTrace() as $trace) {
                $traces[] = [
                    'file' => $trace['file'] ?? '',
                    'line' => $trace['line'] ?? 0,
                    'function' => $trace['function'],
                    'class' => $trace['class'] ?? '',
                    'type' => $trace['type'] ?? '',
                    'args' => array_map(static function ($arg) {
                        if (\is_object($arg)) {
                            return ['object' => $arg::class];
                        }
                        if (\is_array($arg)) {
                            return ['array' => 'Array'];
                        }
                        if (\is_resource($arg)) {
                            return ['resource' => get_resource_type($arg)];
                        }

                        return $arg;
                    }, $trace['args'] ?? []),
                ];
            }
            $data['traces'] = $traces;
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

<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Checker\BasicDataCheckerInterface;
use App\Exception\InvalidArgumentException;
use App\Exception\PeriodException;
use App\Exception\UnexpectedResultException;
use App\Fetcher\SessionFetcherInterface;
use App\Traits\RequestStackTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\Security\Core\Security;

class FinishRequestSubscriber implements EventSubscriberInterface
{
    use RequestStackTrait;

    public bool $checked = false;

    public function __construct(
        private readonly Security $security,
        private readonly SessionFetcherInterface $sessionFetcher,
        private readonly BasicDataCheckerInterface $basicDataChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FinishRequestEvent::class => 'onKernelFinishRequest',
        ];
    }

    /**
     * @throws PeriodException
     * @throws InvalidArgumentException
     */
    public function onKernelFinishRequest(FinishRequestEvent $event = null): void
    {
        if ($this->checked || null === $this->security->getUser()) {
            return;
        }

        $this->sessionFetcher->getPeriodOnSession();
        $this->getFlashBag()->clear();

        $methods = (new \ReflectionClass(BasicDataCheckerInterface::class))->getMethods();
        foreach ($methods as $method) {
            try {
                \call_user_func([$this->basicDataChecker, $method->getName()]);
            } catch (UnexpectedResultException $exception) {
                $this->getFlashBag()->add('danger', $exception->getMessage());

                continue;
            }
        }

        $this->checked = true;
    }
}

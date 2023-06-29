<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Exception\AppException;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly PeriodManager $periodManager,
        private readonly SchoolManager $schoolManager,
        private ?User $user = null,
    ) {
    }

    /**
     * @throws AppException
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): static
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->logger->info('user IS_AUTHENTICATED_FULLY ');
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->logger->info('user IS_AUTHENTICATED_REMEMBERED ');
        }

        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
            throw new AppException('The User is not instance of '.User::class);
        }

        $this->user = $user;
        $this->setDataDefault();

        return $this;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onSecurityInteractiveLogin', 10],
            ],
        ];
    }

    /**
     * @throws AppException
     */
    private function setDataDefault(): void
    {
        $this->logger->debug(__FUNCTION__);

        if (!$this->user instanceof \App\Entity\User) {
            throw new AppException('user is not class object');
        }

        $this->schoolManager->setSchoolsOnSession();

        $this->periodManager->setPeriodsOnSession();
    }
}

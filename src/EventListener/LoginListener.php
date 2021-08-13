<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\School;
use App\Entity\User;
use App\Exception\AppException;
use App\Services\AbstractService;
use App\Traits\PeriodManagerTrait;
use Fardus\Traits\Symfony\Manager\EntityManagerTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener extends AbstractService
{
    use EntityManagerTrait;
    use PeriodManagerTrait;

    private ?User $user = null;

    public function __construct(
        private AuthorizationChecker $authorizationChecker,
        private SessionInterface     $session
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->setSession($session);
    }

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
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
            throw new AppException('The User is not instance of ' . User::class);
        }

        $this->setUser($user)->setDataDefault();

        return $this;
    }

    /**
     * @throws AppException
     */
    public function setDataDefault(): static
    {
        $this->logger->debug(__FUNCTION__);

        if (empty($this->user)) {
            throw new AppException('user is not class object');
        }

        if ($this->user->getSchoolAccessRight()->isEmpty()) {
            /* @var $school School */
            $school = $this->entityManager
                ->getRepository(School::class)
                ->findOneBy([], ['principal' => 'DESC']);

            if (null !== $school) {
                $this->user->addSchoolAccessRight($school);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
            } else {
                $msg = $this->trans('school.not_found', [], 'user');
                $this->session->getFlashBag()->add('error', $msg);
            }
        } else {
            $school = $this->user->getSchoolAccessRight()->current();
        }
        if (null !== $school) {
            $this->session->set('school', clone $school);
        } else {
            $this->logger->warning(__FUNCTION__ . ' Not found school');
        }

        $this->periodManager->setPeriodsOnSession();

        return $this;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}

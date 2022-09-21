<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => [
                ['setAuthor', 10],
                ['hasPassword', 0],
            ],
        ];
    }

    public function setAuthor(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (method_exists($entity, 'setAuthor')) {
            $entity->setAuthor($this->security->getUser());
        }
    }

    public function hasPassword(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        $entity->setPassword($this->hasher->hashPassword($entity, $entity->getPlainPassword()));
    }
}

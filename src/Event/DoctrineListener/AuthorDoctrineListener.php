<?php

declare(strict_types=1);

namespace App\Event\DoctrineListener;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: 'prePersist')]
#[AsDoctrineListener(event: 'preUpdate')]
readonly class AuthorDoctrineListener
{
    public function __construct(private readonly Security $security) {}

    public function __invoke(PrePersistEventArgs|PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof AuthorEntityInterface && $this->security->getUser() instanceof User) {
            $entity->setAuthor($this->security->getUser());
        }
    }
}

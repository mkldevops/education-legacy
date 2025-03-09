<?php

declare(strict_types=1);

namespace App\Event\DoctrineListener;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: 'prePersist')]
#[AsDoctrineListener(event: 'preUpdate')]
readonly class AuthorDoctrineListener
{
    public function __construct(private readonly Security $security) {}

    public function __invoke(EntityInterface $entity): void
    {
        if ($entity instanceof AuthorEntityInterface && $this->security->getUser() instanceof User) {
            $entity->setAuthor($this->security->getUser());
        }
    }
}

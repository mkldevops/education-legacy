<?php

declare(strict_types=1);

namespace App\Event\DoctrineListener;

use App\Entity\Interface\EntityInterface;
use App\Entity\Interface\PublisherEntityInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: 'prePersist')]
#[AsDoctrineListener(event: 'preUpdate')]
readonly class PublisherDoctrineListener
{
    public function __construct(private readonly Security $security) {}

    public function __invoke(EntityInterface $entity): void
    {
        if ($entity instanceof PublisherEntityInterface && $this->security->getUser() instanceof User) {
            $entity->setPublisher($this->security->getUser());
        }
    }
}

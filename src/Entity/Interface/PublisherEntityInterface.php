<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;

interface PublisherEntityInterface
{
    public function getPublisher(): ?User;

    public function setPublisher(?User $publisher): static;
}

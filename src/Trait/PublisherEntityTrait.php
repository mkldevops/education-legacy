<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait PublisherEntityTrait
{
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?User $publisher;

    public function getPublisher(): ?User
    {
        return $this->publisher;
    }

    public function setPublisher(?User $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }
}

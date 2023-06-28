<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait PublisherEntityTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, cascade={"persist"})
     *
     * @ORM\JoinColumn(nullable=true)
     */
    protected null|User|UserInterface $publisher;

    public function getPublisher(): null|User|UserInterface
    {
        return $this->publisher;
    }

    public function setPublisher(User|UserInterface $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }
}

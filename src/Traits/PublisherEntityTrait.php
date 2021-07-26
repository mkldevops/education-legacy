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
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?UserInterface $publisher;

    public function getPublisher(): ?UserInterface
    {
        return $this->publisher;
    }

    public function setPublisher(UserInterface $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }
}

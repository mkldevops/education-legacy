<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait AuthorEntityTrait
{
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    protected ?User $author = null;

    public function getAuthor(): ?User
    {
        return $this->author instanceof User ? $this->author : null;
    }

    public function setAuthor(?User $user): static
    {
        if ($user instanceof User) {
            $this->author = $user;
        }

        return $this;
    }
}

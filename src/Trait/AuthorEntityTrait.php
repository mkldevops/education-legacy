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

    public function setAuthor(?User $author): static
    {
        if ($author instanceof User) {
            $this->author = $author;
        }

        return $this;
    }
}

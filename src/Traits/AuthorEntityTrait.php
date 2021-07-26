<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait AuthorEntityTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, cascade={"persist"})
     */
    protected User $author;

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }
}

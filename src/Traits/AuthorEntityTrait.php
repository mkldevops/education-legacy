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
    protected null|User $author = null;

    public function getAuthor(): null|User
    {
        return $this->author;
    }

    public function setAuthor(null|User $author): self
    {
        if (null !== $author) {
            $this->author = $author;
        }

        return $this;
    }
}

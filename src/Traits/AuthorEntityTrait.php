<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait AuthorEntityTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, cascade={"persist"})
     */
    protected null|User|UserInterface $author = null;

    public function getAuthor(): null|User
    {
        return $this->author;
    }

    public function setAuthor(null|User|UserInterface $author): self
    {
        if ($author !== null) {
            $this->author = $author;
        }

        return $this;
    }
}

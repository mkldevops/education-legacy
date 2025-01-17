<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait AuthorEntityTrait
{
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    protected null|User|UserInterface $author = null;

    public function getAuthor(): null|User
    {
        return $this->author instanceof User ? $this->author : null;
    }

    public function setAuthor(null|User|UserInterface $author): self
    {
        if ($author instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $this->author = $author;
        }

        return $this;
    }
}

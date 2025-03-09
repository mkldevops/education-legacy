<?php

declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\User;

interface AuthorEntityInterface
{
    public function getAuthor(): ?User;

    public function setAuthor(?User $author): static;
}

<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CommentEntityTrait
{
    #[Groups(['comment', 'comment:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}

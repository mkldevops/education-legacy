<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EmailEntityTrait
{
    #[Groups(['email', 'email:write'])]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[ORM\Column(unique: true, nullable: true)]
    protected ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}

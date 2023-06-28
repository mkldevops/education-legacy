<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait CodeEntityTrait
{
    #[Groups(['code', 'code:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[ORM\Column(unique: true)]
    protected ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(\BackedEnum|string $code): static
    {
        $this->code = \is_string($code) ? $code : (string) $code->value;

        return $this;
    }
}

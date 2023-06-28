<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait LabelEntityTrait
{
    #[Groups(['label', 'label:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[ORM\Column]
    protected ?string $label = null;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }
}

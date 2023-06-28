<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EnableEntityTrait
{
    #[Groups(['enable', 'enable:write'])]
    #[Assert\NotNull]
    #[ORM\Column(options: ['default' => true])]
    protected bool $enable = true;

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function enabled(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }
}

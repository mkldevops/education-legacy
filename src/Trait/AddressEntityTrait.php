<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;

trait AddressEntityTrait
{
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $address = null;

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }
}

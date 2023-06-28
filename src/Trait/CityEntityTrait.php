<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;

trait CityEntityTrait
{
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected ?string $city = null;

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }
}

<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ZipEntityTrait
{
    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    protected ?string $zip = null;

    public function setZip(?string $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }
}

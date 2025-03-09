<?php

declare(strict_types=1);

namespace App\Entity\Interface;

interface EntityInterface extends \Stringable
{
    public function getId(): ?int;
}

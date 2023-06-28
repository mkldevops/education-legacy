<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait AmountEntityTrait
{
    #[Groups(['amount', 'amount:write'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[ORM\Column]
    protected float $amount = 0.00;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}

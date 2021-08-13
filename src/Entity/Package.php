<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageRepository;
use App\Traits\SchoolEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass=PackageRepository::class)
 */
class Package
{
    use IdEntityTrait;
    use SchoolEntityTrait;
    use NameEntityTrait;
    use DescriptionEntityTrait;
    use TimestampableEntity;
    use EnableEntityTrait;

    /**
     * @ORM\Column(type="float")
     */
    private float $price = 0.00;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return $this->getNameWithPrice();
    }

    public function getNameWithPrice(): string
    {
        return $this->name . ' ⇒ ' . $this->price . ' €';
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageRepository;
use App\Trait\Accessor\SchoolAccessorTrait;
use App\Trait\DescriptionEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Table]
#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package implements \Stringable
{
    use DescriptionEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SchoolAccessorTrait;
    use TimestampableEntity;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'packages', cascade: ['persist'])]
    protected ?School $school = null;

    #[ORM\Column(type: 'float')]
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
        return $this->name.' ⇒ '.$this->price.' €';
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

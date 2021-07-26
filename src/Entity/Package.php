<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\SchoolEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntity;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Fardus\Traits\Symfony\Entity\NameEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Package.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\PackageRepository")
 */
class Package
{
    use IdEntity;
    use SchoolEntityTrait;
    use NameEntity;
    use DescriptionEntity;
    use TimestampableEntity;
    use EnableEntity;

    /**
     * @ORM\Column(type="float")
     */
    private float $price;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return $this->getNameWithPrice();
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Package
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getNameWithPrice()
    {
        return $this->name.' ⇒ '.$this->price.' €';
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=GradeRepository::class)
 */
class Grade
{
    use IdEntityTrait;
    use NameEntityTrait;
    use EnableEntityTrait;
    use DescriptionEntityTrait;
    use TimestampableEntity;

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}

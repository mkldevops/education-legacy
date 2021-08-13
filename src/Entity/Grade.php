<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntity;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Fardus\Traits\Symfony\Entity\NameEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=GradeRepository::class)
 */
class Grade
{
    use IdEntity;
    use NameEntity;
    use EnableEntity;
    use DescriptionEntity;
    use TimestampableEntity;

    /**
     * Grade constructor.
     */
    public function __construct()
    {
        $this->setEnable(true);
    }

    public function __toString(): string
    {
        return (string)$this->getName();
    }
}

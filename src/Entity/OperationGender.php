<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OperationGenderRepository;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=OperationGenderRepository::class)
 */
class OperationGender
{
    use IdEntityTrait;
    use NameEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;

    public const CODE_CB = 'cb';
    public const CODE_PRLVT = 'prelevement';
    public const CODE_REMISE = 'remise_cheque';
    public const CODE_VIR = 'virement';
    public const CODE_VRSMT = 'versement';
    public const CODE_CHEQUE = 'cheque';

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private ?string $code = null;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}

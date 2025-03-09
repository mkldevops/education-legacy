<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\EntityInterface;
use App\Repository\OperationGenderRepository;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OperationGenderRepository::class)]
class OperationGender implements \Stringable, EntityInterface
{
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use TimestampableEntity;

    /**
     * @var string
     */
    final public const CODE_CB = 'cb';

    /**
     * @var string
     */
    final public const CODE_PRLVT = 'prelevement';

    /**
     * @var string
     */
    final public const CODE_REMISE = 'remise_cheque';

    /**
     * @var string
     */
    final public const CODE_VIR = 'virement';

    /**
     * @var string
     */
    final public const CODE_VRSMT = 'versement';

    /**
     * @var string
     */
    final public const CODE_CHEQUE = 'cheque';

    #[ORM\Column(type: Types::STRING, length: 30, unique: true)]
    private ?string $code = null;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return (string) $this->name;
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

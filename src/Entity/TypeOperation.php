<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TypeOperationRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\DescriptionEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: TypeOperationRepository::class)]
class TypeOperation implements \Stringable
{
    use AuthorEntityTrait;
    use DescriptionEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var string
     */
    final public const TYPE_AMOUNT_NEGATIVE = 'negative';

    /**
     * @var string
     */
    final public const TYPE_AMOUNT_POSITIVE = 'positive';

    /**
     * @var string
     */
    final public const TYPE_AMOUNT_MIXTE = 'mixte';

    /**
     * @var string
     */
    final public const TYPE_CODE_PAYMENT_PACKAGE_STUDENT = 'PPS';

    /**
     * @var string
     */
    final public const TYPE_CODE_SPLIT = 'SPLIT';

    /**
     * @var string
     */
    final public const TYPE_CODE_TO_DEFINE = 'TO_DEFINE';

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    protected Collection $typeOperations;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'typeOperations')]
    private ?TypeOperation $parent = null;

    #[ORM\Column(type: 'string')]
    private ?string $shortName = null;

    #[ORM\Column(type: 'string', length: 10, unique: true, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'mixte'])]
    private string $typeAmount = self::TYPE_AMOUNT_MIXTE;

    #[ORM\Column(type: 'boolean')]
    private bool $isInternalTransfert = false;

    public function __construct()
    {
        $this->typeOperations = new ArrayCollection();
        $this->enable = true;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function addTypeOperation(self $typeOperations): self
    {
        if (!$this->typeOperations->contains($typeOperations)) {
            $this->typeOperations[] = $typeOperations;
        }

        return $this;
    }

    public function removeTypeOperation(self $typeOperations): void
    {
        $this->typeOperations->removeElement($typeOperations);
    }

    public function getTypeOperations(): Collection
    {
        return $this->typeOperations;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTypeAmount(): string
    {
        return $this->typeAmount;
    }

    public function setTypeAmount(string $typeAmount): self
    {
        $this->typeAmount = $typeAmount;

        return $this;
    }

    /**
     * Get isInternalTransfert.
     */
    public function getIsInternalTransfert(): bool
    {
        return $this->isInternalTransfert;
    }

    /**
     * Set isInternalTransfert.
     */
    public function setIsInternalTransfert(bool $isInternalTransfert): self
    {
        $this->isInternalTransfert = $isInternalTransfert;

        return $this;
    }
}

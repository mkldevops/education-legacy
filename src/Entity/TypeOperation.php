<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TypeOperationRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\DescriptionEntity;
use Fardus\Traits\Symfony\Entity\DescriptionEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntity;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TypeOperationRepository::class)
 */
class TypeOperation
{
    use IdEntityTrait;
    use NameEntityTrait;
    use DescriptionEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    public const TYPE_AMOUNT_NEGATIVE = 'negative';
    public const TYPE_AMOUNT_POSITIVE = 'positive';
    public const TYPE_AMOUNT_MIXTE = 'mixte';

    public const TYPE_CODE_PAYMENT_PACKAGE_STUDENT = 'PPS';
    public const TYPE_CODE_SPLIT = 'SPLIT';
    public const TYPE_CODE_TO_DEFINE = 'TO_DEFINE';

    /**
     * @ORM\ManyToOne(targetEntity=TypeOperation::class, inversedBy="typeOperations")
     */
    private ?TypeOperation $parent = null;

    /**
     * @ORM\OneToMany(targetEntity=TypeOperation::class, mappedBy="parent")
     */
    protected Collection $typeOperations;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $shortName = null;

    /**
     * @ORM\Column(type="string", length=10, unique=true, nullable=true)
     */
    private ?string $code = null;

    /**
     * @ORM\Column(type="string", length=10, options={"default" = "mixte"})
     */
    private string $typeAmount = self::TYPE_AMOUNT_MIXTE;

    /**
     * @ORM\Column(type="boolean")
     */
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

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setParent(?TypeOperation $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function addTypeOperation(TypeOperation $typeOperations): self
    {
        if (!$this->typeOperations->contains($typeOperations)) {
            $this->typeOperations[] = $typeOperations;
        }

        return $this;
    }

    public function removeTypeOperation(TypeOperation $typeOperations): void
    {
        $this->typeOperations->removeElement($typeOperations);
    }

    public function getTypeOperations(): Collection
    {
        return $this->typeOperations;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setTypeAmount(string $typeAmount): self
    {
        $this->typeAmount = $typeAmount;

        return $this;
    }

    public function getTypeAmount()
    {
        return $this->typeAmount;
    }

    /**
     * Set isInternalTransfert.
     */
    public function setIsInternalTransfert(bool $isInternalTransfert): self
    {
        $this->isInternalTransfert = $isInternalTransfert;

        return $this;
    }

    /**
     * Get isInternalTransfert.
     *
     * @return bool
     */
    public function getIsInternalTransfert()
    {
        return $this->isInternalTransfert;
    }
}

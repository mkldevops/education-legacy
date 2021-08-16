<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 */
class Account
{
    use IdEntityTrait;
    use TimestampableEntity;
    use EnableEntityTrait;
    use NameEntityTrait;

    public const DEFAULT_INTERVAL_STATEMENT = 5;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class, inversedBy="accounts")
     */
    private ?Structure $structure = null;

    /**
     * @ORM\OneToMany(targetEntity=Operation::class, mappedBy="account")
     */
    private Collection $operations;

    /**
     * @ORM\OneToMany(targetEntity=AccountStatement::class, mappedBy="account")
     */
    private Collection $accountStatements;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $principal = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isBank = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $enableAccountStatement = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $bankName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $bankAddress = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $bankIban = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $bankBic = null;

    /**
     * @ORM\Column(type="integer", options={"default" : "5"})
     */
    private int $intervalOperationsAccountStatement = self::DEFAULT_INTERVAL_STATEMENT;

    #[Pure]
    public function __construct()
    {
        $this->accountStatements = new ArrayCollection();
        $this->operations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->name;
    }

    public function addOperation(Operation $operations): self
    {
        $this->operations[] = $operations;

        return $this;
    }

    public function removeOperation(Operation $operations): self
    {
        $this->operations->removeElement($operations);

        return $this;
    }

    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function getPrincipal(): bool
    {
        return $this->principal;
    }

    public function setPrincipal(bool $principal): self
    {
        $this->principal = $principal;

        return $this;
    }

    public function addAccountStatement(AccountStatement $accountStatements): self
    {
        $this->accountStatements[] = $accountStatements;

        return $this;
    }

    public function removeAccountStatement(AccountStatement $accountStatements): self
    {
        $this->accountStatements->removeElement($accountStatements);

        return $this;
    }

    public function getAccountStatements(): Collection
    {
        return $this->accountStatements;
    }

    public function getIsBank(): bool
    {
        return $this->isBank;
    }

    public function setIsBank(bool $isBank): self
    {
        $this->isBank = $isBank;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankAddress(): ?string
    {
        return $this->bankAddress;
    }

    public function setBankAddress(string $bankAddress): self
    {
        $this->bankAddress = $bankAddress;

        return $this;
    }

    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    public function setBankIban(string $bankIban): self
    {
        $this->bankIban = $bankIban;

        return $this;
    }

    public function getBankBic(): ?string
    {
        return $this->bankBic;
    }

    public function setBankBic(string $bankBic): self
    {
        $this->bankBic = $bankBic;

        return $this;
    }

    public function getIntervalOperationsAccountStatement(): int
    {
        return $this->intervalOperationsAccountStatement;
    }

    public function setIntervalOperationsAccountStatement(int $intervalOperationsAccountStatement): self
    {
        $this->intervalOperationsAccountStatement = $intervalOperationsAccountStatement;

        return $this;
    }

    public function getEnableAccountStatement(): bool
    {
        return $this->enableAccountStatement;
    }

    public function setEnableAccountStatement(bool $enableAccountStatement): self
    {
        $this->enableAccountStatement = $enableAccountStatement;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }
}

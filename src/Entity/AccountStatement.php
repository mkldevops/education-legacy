<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AccountStatementRepository;
use App\Traits\AuthorEntityTrait;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=AccountStatementRepository::class)
 */
class AccountStatement
{
    use IdEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;

    /**
     * @var Collection|Operation[]
     * @ORM\OneToMany(targetEntity=Operation::class, mappedBy="accountStatement")
     */
    protected array|Collection $operations;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $title = null;
    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $begin;
    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $end;
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private \DateTime $month;
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $amountCredit = 0.00;
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $amountDebit = 0.00;
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $newBalance = 0.00;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $numberOperations = 0;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $reference = null;
    /**
     * @ORM\ManyToMany(targetEntity=Document::class, cascade={"persist"}, inversedBy="accountStatements")
     */
    private Collection $documents;
    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="accountStatements", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private Account $account;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->month->format('F Y');
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    public function setBegin(\DateTime $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function addDocument(Document $documents): self
    {
        $this->documents[] = $documents;

        return $this;
    }

    public function removeDocument(Document $documents): self
    {
        $this->documents->removeElement($documents);

        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations->add($operation);
        }

        return $this;
    }

    public function removeOperation(Operation $operations): self
    {
        $this->operations->removeElement($operations);

        return $this;
    }

    public function getOperations(): array|Collection
    {
        return $this->operations;
    }

    public function calcAmount(): self
    {
        foreach ($this->operations as $operation) {
            $this->addAmount((float) $operation->getAmount());
        }

        return $this;
    }

    public function addAmount(float $amount): self
    {
        if ($amount > 0) {
            $this->addAmountCredit($amount);
        } else {
            $this->addAmountDebit($amount);
        }

        return $this;
    }

    public function addAmountCredit(float $amountCredit): self
    {
        $this->setAmountCredit($this->amountCredit + abs($amountCredit));

        return $this;
    }

    public function addAmountDebit(float $amountDebit): self
    {
        $this->setAmountDebit($this->amountDebit - abs($amountDebit));

        return $this;
    }

    public function getAmountCredit(): float
    {
        return $this->amountCredit ?? 0.00;
    }

    public function setAmountCredit(float $amountCredit): self
    {
        $this->amountCredit = abs($amountCredit);

        return $this;
    }

    public function getAmountDebit(): float
    {
        return $this->amountDebit ?? 0.00;
    }

    public function setAmountDebit(float $amountDebit): self
    {
        $this->amountDebit = 0 - abs($amountDebit);

        return $this;
    }

    public function getNewBalance(): float
    {
        return $this->newBalance ?? 0.00;
    }

    public function setNewBalance(float $newBalance): self
    {
        $this->newBalance = $newBalance;

        return $this;
    }

    public function calcNumberOperations(): self
    {
        $this->numberOperations = $this->operations->count();

        return $this;
    }

    public function getNumberOperations(): int
    {
        return $this->numberOperations ?? 0;
    }

    public function setNumberOperations(int $numberOperations): self
    {
        $this->numberOperations = $numberOperations;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getMonth(): \DateTime
    {
        return $this->month;
    }

    public function setMonth(\DateTime $month): self
    {
        $this->month = $month;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OperationRepository;
use App\Traits\AmountEntityTrait;
use App\Traits\AuthorEntityTrait;
use App\Traits\PublisherEntityTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(indexes={@ORM\Index(columns={"account_id"}), @ORM\Index(columns={"date", "date_planned"})})
 * @ORM\Entity(repositoryClass=OperationRepository::class)
 */
class Operation
{
    use AmountEntityTrait;
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdentityTrait;
    use NameEntityTrait;
    use PublisherEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Account $account = null;

    /**
     * @ORM\ManyToOne(targetEntity=AccountStatement::class, inversedBy="operations", cascade={"persist"})
     */
    private ?AccountStatement $accountStatement = null;

    /**
     * @ORM\ManyToOne(targetEntity=TypeOperation::class)
     */
    private ?TypeOperation $typeOperation;

    /**
     * @ORM\ManyToOne(targetEntity=Validate::class, cascade={"persist"})
     */
    private ?Validate $validate;

    /**
     * @ORM\ManyToMany(targetEntity=Document::class, inversedBy="operations", cascade={"remove"})
     */
    private Collection $documents;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $reference;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private ?string $uniqueId = null;

    /**
     * @ORM\ManyToOne(targetEntity=OperationGender::class)
     */
    private ?OperationGender $operationGender = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var null|\DateTime|\DateTimeImmutable
     */
    private ?\DateTimeInterface $date;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var null|\DateTime|\DateTimeImmutable
     */
    private ?\DateTimeInterface $datePlanned;

    /**
     * @ORM\OneToMany(targetEntity=PaymentPackageStudent::class, mappedBy="operation", cascade={"remove"})
     */
    private Collection $paymentPackageStudents;

    /**
     * @ORM\OneToOne(targetEntity=AccountSlip::class, mappedBy="operationDebit", cascade={"persist"})
     */
    private ?AccountSlip $slipsDebit;

    /**
     * @ORM\OneToOne(targetEntity=AccountSlip::class, mappedBy="operationCredit", cascade={"persist"})
     */
    private ?AccountSlip $slipsCredit;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->documents = new ArrayCollection();
        $this->paymentPackageStudents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return null|\DateTime|\DateTimeImmutable
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param \DateTime|\DateTimeImmutable $date
     */
    public function setDate(\DateTimeInterface $date, bool $force = false): self
    {
        $this->date = $date;
        $this->datePlanned = $date;

        if (!$force && $date->getTimestamp() > time()) {
            $this->date = null;
        }

        return $this;
    }

    /**
     * @return null|\DateTime|\DateTimeImmutable
     */
    public function getValueDate(): ?\DateTimeInterface
    {
        return $this->date ?? $this->datePlanned;
    }

    /**
     * @return null|\DateTime|\DateTimeImmutable
     */
    public function getDatePlanned(): ?\DateTimeInterface
    {
        return $this->datePlanned;
    }

    /**
     * @param null|\DateTime|\DateTimeImmutable $datePlanned
     */
    public function setDatePlanned(?\DateTimeInterface $datePlanned): self
    {
        $this->datePlanned = $datePlanned;

        return $this;
    }

    public function isPlanned(): bool
    {
        return !(bool) $this->date;
    }

    public function getTypeOperation(): ?TypeOperation
    {
        return $this->typeOperation;
    }

    public function setTypeOperation(?TypeOperation $typeOperation): self
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    public function getPaymentPackageStudents(): Collection
    {
        return $this->paymentPackageStudents;
    }

    public function addPaymentPackageStudents(PaymentPackageStudent $paymentPackageStudent): self
    {
        $this->paymentPackageStudents[] = $paymentPackageStudent;

        return $this;
    }

    public function removePaymentPackageStudents(PaymentPackageStudent $paymentPackageStudent): void
    {
        $this->paymentPackageStudents->removeElement($paymentPackageStudent);
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

    public function getOperationGender(): ?OperationGender
    {
        return $this->operationGender;
    }

    public function setOperationGender(?OperationGender $operationGender): self
    {
        $this->operationGender = $operationGender;

        return $this;
    }

    public function hasStructure(?Structure $structure): bool
    {
        return $this->account->getStructure()?->getId() === $structure?->getId();
    }

    public function getValidate(): ?Validate
    {
        return $this->validate;
    }

    public function setValidate(Validate $validate = null): self
    {
        $this->validate = $validate;

        return $this;
    }

    public function hasErrorAmount(): bool
    {
        $result = false;
        $i = $this->typeOperation?->getTypeAmount();
        if (TypeOperation::TYPE_AMOUNT_NEGATIVE === $i) {
            $result = ($this->amount > 0);
        } elseif (TypeOperation::TYPE_AMOUNT_POSITIVE === $i) {
            $result = ($this->amount < 0);
        }

        return $result;
    }

    public function addDocument(Document $documents): self
    {
        $this->documents[] = $documents;

        return $this;
    }

    public function removeDocument(Document $documents): void
    {
        $this->documents->removeElement($documents);
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getAccountStatement(): ?AccountStatement
    {
        return $this->accountStatement;
    }

    public function setAccountStatement(AccountStatement $accountStatement = null): static
    {
        $this->accountStatement = $accountStatement;

        return $this;
    }

    public function hasAccountStatement(): bool
    {
        return $this->accountStatement instanceof AccountStatement;
    }

    public function getSlipsDebit(): ?AccountSlip
    {
        return $this->slipsDebit;
    }

    public function setSlipsDebit(AccountSlip $slipsDebit = null): static
    {
        $this->slipsDebit = $slipsDebit;

        return $this;
    }

    public function getSlipsCredit(): ?AccountSlip
    {
        return $this->slipsCredit;
    }

    public function setSlipsCredit(AccountSlip $slipsCredit): self
    {
        $this->slipsCredit = $slipsCredit;

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId = null): self
    {
        if ($this->account->getIsBank()) {
            $this->uniqueId = $uniqueId;
        }

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }
}

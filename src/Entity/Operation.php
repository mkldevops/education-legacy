<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Entity\Interface\PublisherEntityInterface;
use App\Repository\OperationRepository;
use App\Trait\AmountEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\PublisherEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Index(columns: ['account_id'])]
#[ORM\Index(columns: ['date', 'date_planned'])]
#[ORM\Entity(repositoryClass: OperationRepository::class)]
class Operation implements EntityInterface, PublisherEntityInterface, AuthorEntityInterface
{
    use AmountEntityTrait;
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use PublisherEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\ManyToOne(targetEntity: AccountStatement::class, inversedBy: 'operations', cascade: ['persist'])]
    private ?AccountStatement $accountStatement = null;

    #[ORM\ManyToOne(targetEntity: TypeOperation::class)]
    private ?TypeOperation $typeOperation = null;

    #[ORM\ManyToOne(targetEntity: Validate::class, cascade: ['persist'])]
    private ?Validate $validate = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'operations', cascade: ['remove'])]
    private Collection $documents;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    private ?string $uniqueId = null;

    #[ORM\ManyToOne(targetEntity: OperationGender::class)]
    private ?OperationGender $operationGender = null;

    /**
     * @var null|\DateTime|\DateTimeImmutable
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date;

    /**
     * @var null|\DateTime|\DateTimeImmutable
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePlanned = null;

    /**
     * @var Collection<int, PaymentPackageStudent>
     */
    #[ORM\OneToMany(targetEntity: PaymentPackageStudent::class, mappedBy: 'operation', cascade: ['remove'])]
    private Collection $paymentPackageStudents;

    #[ORM\OneToOne(targetEntity: AccountSlip::class, mappedBy: 'operationDebit', cascade: ['persist'])]
    private ?AccountSlip $slipsDebit = null;

    #[ORM\OneToOne(targetEntity: AccountSlip::class, mappedBy: 'operationCredit', cascade: ['persist'])]
    private ?AccountSlip $slipsCredit = null;

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

    public function setDate(\DateTime|\DateTimeImmutable $date, bool $force = false): self
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

    public function setDatePlanned(\DateTime|\DateTimeImmutable|null $datePlanned): self
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

    /**
     * @return Collection<int, PaymentPackageStudent>
     */
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

    public function setValidate(?Validate $validate = null): self
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

    public function addDocument(Document $document): self
    {
        $this->documents[] = $document;

        return $this;
    }

    public function removeDocument(Document $document): void
    {
        $this->documents->removeElement($document);
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getAccountStatement(): ?AccountStatement
    {
        return $this->accountStatement;
    }

    public function setAccountStatement(?AccountStatement $accountStatement = null): static
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

    public function setSlipsDebit(?AccountSlip $accountSlip = null): static
    {
        $this->slipsDebit = $accountSlip;

        return $this;
    }

    public function getSlipsCredit(): ?AccountSlip
    {
        return $this->slipsCredit;
    }

    public function setSlipsCredit(AccountSlip $accountSlip): self
    {
        $this->slipsCredit = $accountSlip;

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(?string $uniqueId = null): self
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

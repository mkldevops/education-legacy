<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OperationRepository;
use App\Traits\AmountEntityTrait;
use App\Traits\AuthorEntityTrait;
use App\Traits\PublisherEntityTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntity;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Fardus\Traits\Symfony\Entity\NameEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(indexes={@ORM\Index(columns={"account_id"}), @ORM\Index(columns={"date", "date_planned"})})
 * @ORM\Entity(repositoryClass=OperationRepository::class)
 */
class Operation
{
    use IdEntity;
    use NameEntity;
    use AuthorEntityTrait;
    use EnableEntity;
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use CommentEntity;
    use PublisherEntityTrait;
    use AmountEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private Account $account;

    /**
     * @ORM\ManyToOne(targetEntity=AccountStatement::class, inversedBy="operations", cascade={"persist"})
     */
    private AccountStatement $accountStatement;

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
     */
    private ?DateTimeInterface $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $datePlanned;

    /**
     * @ORM\OneToOne(targetEntity=PaymentPackageStudent::class, mappedBy="operation")
     */
    private ?PaymentPackageStudent $paymentPackageStudent;

    /**
     * @ORM\OneToOne(targetEntity=AccountSlip::class, mappedBy="operationDebit", cascade={"persist"})
     */
    private ?AccountSlip $slipsDebit;

    /**
     * @ORM\OneToOne(targetEntity=AccountSlip::class, mappedBy="operationCredit", cascade={"persist"})
     */
    private ?AccountSlip $slipsCredit;

    /**
     * Constructor Operation.
     */
    public function __construct()
    {
        $this->setDate(new Datetime())
            ->setEnable(true)
            ->documents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setDate(DateTimeInterface $date, bool $force = false): self
    {
        $this->date = $date;
        $this->datePlanned = $date;

        if (!$force && $date->getTimestamp() > time()) {
            $this->date = null;
        }

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function getValueDate(): ?DateTimeInterface
    {
        return $this->date ?? $this->datePlanned;
    }

    public function setDatePlanned(?DateTimeInterface $datePlanned): self
    {
        $this->datePlanned = $datePlanned;

        return $this;
    }

    public function getDatePlanned(): ?DateTimeInterface
    {
        return $this->datePlanned;
    }

    public function isPlanned(): bool
    {
        return !(bool) $this->date;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return Account
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * Set typeOperation.
     */
    public function setTypeOperation(TypeOperation $typeOperation): self
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    /**
     * Get typeOperation.
     *
     * @return TypeOperation
     */
    public function getTypeOperation(): ?TypeOperation
    {
        return $this->typeOperation;
    }

    /**
     * Set PaymentPackageStudent.
     */
    public function setPaymentPackageStudent(PaymentPackageStudent $paymentPackageStudent = null): self
    {
        $this->paymentPackageStudent = $paymentPackageStudent;

        return $this;
    }

    /**
     * Get PaymentPackageStudent.
     *
     * @return PaymentPackageStudent
     */
    public function getPaymentPackageStudent(): ?PaymentPackageStudent
    {
        return $this->paymentPackageStudent;
    }

    /**
     * Set reference.
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set gender to Operation.
     */
    public function setOperationGender(?OperationGender $operationGender): self
    {
        $this->operationGender = $operationGender;

        return $this;
    }

    /**
     * @return OperationGender
     */
    public function getOperationGender(): ?OperationGender
    {
        return $this->operationGender;
    }

    /**
     * Check if school is good.
     */
    public function hasStructure(Structure $structure): bool
    {
        return $this->account->getStructure() instanceof Structure && $this->account->getStructure()->getId() === $structure->getId();
    }

    /**
     * Set validate.
     */
    public function setValidate(Validate $validate = null): self
    {
        $this->validate = $validate;

        return $this;
    }

    /**
     * Get validate.
     *
     * @return Validate
     */
    public function getValidate(): ?Validate
    {
        return $this->validate;
    }

    /**
     * Get has Error Amount.
     */
    public function hasErrorAmount(): bool
    {
        $result = false;
        $i = $this->typeOperation->getTypeAmount();
        if (TypeOperation::TYPE_AMOUNT_NEGATIVE == $i) {
            $result = ($this->amount > 0);
        } elseif (TypeOperation::TYPE_AMOUNT_POSITIVE == $i) {
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

    /**
     * Get documents.
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * Set accountStatement.
     *
     * @return Operation
     */
    public function setAccountStatement(AccountStatement $accountStatement = null)
    {
        $this->accountStatement = $accountStatement;

        return $this;
    }

    /**
     * Get accountStatement.
     *
     * @return AccountStatement
     */
    public function getAccountStatement(): ?AccountStatement
    {
        return $this->accountStatement;
    }

    /**
     * Has accountStatement.
     */
    public function hasAccountStatement(): bool
    {
        return $this->accountStatement instanceof AccountStatement;
    }

    /**
     * Set slipsDebit.
     *
     * @return Operation
     */
    public function setSlipsDebit(AccountSlip $slipsDebit = null)
    {
        $this->slipsDebit = $slipsDebit;

        return $this;
    }

    public function getSlipsDebit(): AccountSlip
    {
        return $this->slipsDebit;
    }

    public function setSlipsCredit(AccountSlip $slipsCredit): self
    {
        $this->slipsCredit = $slipsCredit;

        return $this;
    }

    /**
     * Get slipsCredit.
     *
     * @return AccountSlip
     */
    public function getSlipsCredit(): ?AccountSlip
    {
        return $this->slipsCredit;
    }

    public function setUniqueId(string $uniqueId = null): self
    {
        if ($this->getAccount() instanceof Account && $this->getAccount()->getIsBank()) {
            $this->uniqueId = $uniqueId;
        }

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }
}

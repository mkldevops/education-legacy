<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Repository\AccountSlipRepository;
use App\Trait\AmountEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AccountSlipRepository::class)]
#[UniqueEntity(fields: ['structure', 'gender', 'reference'])]
class AccountSlip implements \Stringable
{
    use AmountEntityTrait;
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    /**
     * @var string
     */
    final public const GENDER_BANK_TRANSFER = 'virement';

    /**
     * @var string
     */
    final public const GENDER_PAYMENT_SPECIES = 'versement';

    /**
     * @var string
     */
    final public const GENDER_REBATE_CHECK = 'remise_cheque';

    /**
     * @var string
     */
    final public const GENDER_CASH_WITHDRAWAL = 'cash_withdrawal';

    /**
     * @var string
     */
    final public const TYPE_DEBIT = 'debit';

    /**
     * @var string
     */
    final public const TYPE_CREDIT = 'credit';

    #[ORM\OneToOne(targetEntity: Operation::class, inversedBy: 'slipsCredit', cascade: ['persist'])]
    protected ?Operation $operationCredit = null;

    #[ORM\OneToOne(targetEntity: Operation::class, inversedBy: 'slipsDebit', cascade: ['persist'])]
    protected ?Operation $operationDebit = null;

    #[ORM\ManyToMany(targetEntity: Document::class, cascade: ['persist'], inversedBy: 'accountSlips')]
    protected Collection $documents;

    #[ORM\Column(type: 'datetime')]
    protected ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 20)]
    protected string $gender;

    #[ORM\Column(type: 'string', length: 40, nullable: false)]
    protected string $reference;

    #[ORM\Column(type: 'string', length: 100, nullable: true, unique: true)]
    protected ?string $uniqueId = null;

    #[ORM\ManyToOne(targetEntity: Structure::class, cascade: ['persist'], inversedBy: 'accountSlips')]
    protected Structure $structure;

    public function __construct()
    {
        $this->setDate(new \DateTime())
            ->setEnable(true)
            ->documents = new ArrayCollection()
        ;
    }

    public function __toString(): string
    {
        return $this->getName().' - '.$this->getDate()->format('d M Y');
    }

    public static function getGenders(): array
    {
        return [
            self::GENDER_BANK_TRANSFER => self::GENDER_BANK_TRANSFER,
            self::GENDER_PAYMENT_SPECIES => self::GENDER_PAYMENT_SPECIES,
            self::GENDER_REBATE_CHECK => self::GENDER_REBATE_CHECK,
            self::GENDER_CASH_WITHDRAWAL => self::GENDER_CASH_WITHDRAWAL,
        ];
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getErrors(): array
    {
        $errors = [];

        if ($this->hasOperationCredit() && $this->hasOperationDebit()) {
            if ($this->getOperationDebit()?->getAccount() === $this->getOperationCredit()?->getAccount()) {
                $errors[] = 'error.same_account';
            }

            if ($this->getOperationCredit()?->getAmount() < 0) {
                $errors[] = 'error.credit_negative';
            }

            if ($this->getOperationDebit()?->getAmount() > 0) {
                $errors[] = 'error.debit_positive';
            }
        } else {
            $errors[] = 'error.nothing_operation';
        }

        return $errors;
    }

    public function hasOperationCredit(): bool
    {
        return !empty($this->operationCredit);
    }

    public function hasOperationDebit(): bool
    {
        return !empty($this->operationDebit);
    }

    public function getOperationDebit(): ?Operation
    {
        return $this->operationDebit;
    }

    public function setOperationDebit(Operation $operationDebit): self
    {
        $this->operationDebit = $operationDebit;

        return $this;
    }

    public function getOperationCredit(): ?Operation
    {
        return $this->operationCredit;
    }

    public function setOperationCredit(Operation $operationCredit): self
    {
        $this->operationCredit = $operationCredit;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getOperation(string $type): ?Operation
    {
        if (!\in_array($type, [self::TYPE_CREDIT, self::TYPE_DEBIT], true)) {
            throw new AppException(sprintf("The type \"%s\" of operation don't supported", $type));
        }

        $operation = $this->getOperationCredit();
        if (self::TYPE_DEBIT === $type) {
            return $this->getOperationDebit();
        }

        return $operation;
    }

    /**
     * @throws AppException
     */
    public function hasOperation(string $type): bool
    {
        if (!\in_array($type, [self::TYPE_CREDIT, self::TYPE_DEBIT], true)) {
            throw new AppException(sprintf("The type \"%s\" of operation don't supported", $type));
        }

        $result = $this->hasOperationCredit();
        if (self::TYPE_DEBIT === $type) {
            return $this->hasOperationDebit();
        }

        return $result;
    }

    /**
     * @throws AppException
     */
    public function setOperation(Operation $operation, string $type): Operation
    {
        if (\in_array($type, [self::TYPE_CREDIT, self::TYPE_DEBIT], true)) {
            self::TYPE_DEBIT === $type ? $this->setOperationDebit($operation) : $this->setOperationCredit($operation);
        } else {
            throw new AppException(sprintf("The type \"%s\" of operation don't exists", $type));
        }

        return $operation;
    }

    public function getAmount(string $type = null): float
    {
        return self::TYPE_DEBIT === $type ? -1 * abs($this->amount) : abs($this->amount);
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        if ($this->getOperationCredit() instanceof Operation) {
            $this->getOperationCredit()->setAmount($this->amount);
        }

        if ($this->getOperationDebit() instanceof Operation) {
            $this->getOperationDebit()->setAmount($this->amount);
        }

        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
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

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId): self
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    public function getStructure(): Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }
}

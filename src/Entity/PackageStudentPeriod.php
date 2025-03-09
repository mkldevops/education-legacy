<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\PackageStudentPeriodRepository;
use App\Trait\AmountEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\StudentEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\UniqueConstraint(fields: ['package', 'period', 'student'])]
#[ORM\Entity(repositoryClass: PackageStudentPeriodRepository::class)]
#[UniqueEntity(fields: ['package', 'period', 'student'], groups: ['registration'])]
class PackageStudentPeriod implements \Stringable, EntityInterface, AuthorEntityInterface
{
    use AmountEntityTrait;
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use IdEntityTrait;
    use StudentEntityTrait;
    use TimestampableEntity;

    /**
     * @var string
     */
    final public const STATUS_PAYMENT_INFO = 'info';

    /**
     * @var string
     */
    final public const STATUS_PAYMENT_SUCCESS = 'success';

    /**
     * @var string
     */
    final public const STATUS_PAYMENT_WARNING = 'warning';

    /**
     * @var string
     */
    final public const STATUS_PAYMENT_DANGER = 'danger';

    /**
     * @var int
     */
    final public const DIFF_UNPAID_PERCENT = 20;

    #[ORM\ManyToOne(targetEntity: Package::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?Package $package = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Student::class, cascade: ['persist', 'remove'], inversedBy: 'packagePeriods')]
    protected ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Period::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Period $period = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTime = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $discount = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $paid = false;

    /**
     * @var Collection<int, PaymentPackageStudent>
     */
    #[ORM\OneToMany(mappedBy: 'packageStudentPeriod', targetEntity: PaymentPackageStudent::class)]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getStudent().' - '.$this->getPackage();
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getDateExpire(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateExpire(\DateTimeInterface $dateExpire): self
    {
        $this->dateTime = $dateExpire;

        return $this;
    }

    public function getUnpaid(): float
    {
        return $this->student?->getEnable() ? ($this->amount - $this->discount - $this->getAmountPayments()) : 0.00;
    }

    public function getAmountPayments(): float|int
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->getAmount();
        }

        return $amount;
    }

    public function getStatusPayments(): string
    {
        return self::getStatusPaymentsStatic($this->getPercentPayments(), $this->period);
    }

    public static function getStatusPaymentsStatic(float $percentPayment, Period $period): string
    {
        $status = self::STATUS_PAYMENT_INFO;

        if ($percentPayment >= 100) {
            $status = self::STATUS_PAYMENT_SUCCESS;
        } elseif ($percentPayment < $period->getPercent()) {
            $diff = $period->getPercent() - $percentPayment;

            $status = $diff > self::DIFF_UNPAID_PERCENT ? self::STATUS_PAYMENT_DANGER : self::STATUS_PAYMENT_WARNING;
        }

        return $status;
    }

    public function getPercentPayments(): float
    {
        return ($this->getAmountPayments() + $this->discount) / ($this->amount ?: $this->package->getPrice()) * 100;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaidStr(): string
    {
        return $this->paid ? 'non' : 'oui';
    }

    public function addPayment(PaymentPackageStudent $paymentPackageStudent): static
    {
        $this->payments[] = $paymentPackageStudent;

        return $this;
    }

    public function removePayment(PaymentPackageStudent $paymentPackageStudent): void
    {
        $this->payments->removeElement($paymentPackageStudent);
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }
}

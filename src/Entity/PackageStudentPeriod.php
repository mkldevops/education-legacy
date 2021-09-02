<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageStudentPeriodRepository;
use App\Traits\AmountEntityTrait;
use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"package_id", "period_id", "student_id"})})
 * @ORM\Entity(repositoryClass=PackageStudentPeriodRepository::class)
 * @UniqueEntity(fields={"package", "period", "student"}, groups={"registration"})
 */
class PackageStudentPeriod
{
    use IdEntityTrait;
    use TimestampableEntity;
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use StudentEntityTrait;
    use AmountEntityTrait;

    public const STATUS_PAYMENT_INFO = 'info';
    public const STATUS_PAYMENT_SUCCESS = 'success';
    public const STATUS_PAYMENT_WARNING = 'warning';
    public const STATUS_PAYMENT_DANGER = 'danger';
    public const DIFF_UNPAID_PERCENT = 20;

    /**
     * @ORM\ManyToOne(targetEntity=Package::class)
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Package $package = null;

    /**
     * @ORM\ManyToOne(targetEntity=Period::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Period $period = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $dateExpire;

    /**
     * @ORM\Column(type="float")
     */
    private float $discount = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $paid = false;

    /**
     * @ORM\OneToMany(targetEntity=PaymentPackageStudent::class, mappedBy="packageStudentPeriod")
     */
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

    public function getDateExpire(): ?DateTimeInterface
    {
        return $this->dateExpire;
    }

    public function setDateExpire(DateTimeInterface $dateExpire): self
    {
        $this->dateExpire = $dateExpire;

        return $this;
    }

    /**
     * Get amount unpaid.
     */
    public function getUnpaid(): float
    {
        return $this->amount - $this->discount - $this->getAmountPayments();
    }

    /**
     * Get amount.
     */
    public function getAmountPayments(): float|int
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->getOperation()->getAmount();
        }

        return $amount;
    }

    /**
     * Get amount unpaid.
     */
    public function getStatusPayments(): string
    {
        return self::getStatusPaymentsStatic($this->getPercentPayments(), $this->period);
    }

    public static function getStatusPaymentsStatic($percentPayment, Period $period): string
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

    /**
     * Get amount unpaid.
     */
    public function getPercentPayments(): float
    {
        return ($this->getAmountPayments() + $this->discount) / ($this->amount ?: $this->package->getPrice()) * 100;
    }

    /**
     * Get discount.
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * Set discount.
     */
    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get paid.
     */
    public function getPaid(): bool
    {
        return $this->paid;
    }

    /**
     * Set paid.
     */
    public function setPaid(bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid.
     */
    public function getPaidStr(): string
    {
        return $this->paid ? 'non' : 'oui';
    }

    /**
     * Add payments.
     */
    public function addPayment(PaymentPackageStudent $payments): static
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments.
     */
    public function removePayment(PaymentPackageStudent $payments): void
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments.
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }
}

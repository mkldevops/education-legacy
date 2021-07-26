<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageStudentPeriodRepository;
use App\Traits\AmountEntityTrait;
use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"package_id", "period_id", "student_id"})})
 * @ORM\Entity(repositoryClass=PackageStudentPeriodRepository::class)
 * @UniqueEntity(fields={"package", "period", "student"}, groups={"registration"})
 */
class PackageStudentPeriod
{
    use IdEntity;
    use TimestampableEntity;
    use AuthorEntityTrait;
    use CommentEntity;
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
    private ?DateTime $dateExpire;

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

    public function getDateExpire(): \DateTimeInterface
    {
        return $this->dateExpire;
    }

    /**
     * Set dateExpire.
     *
     * @param DateTime $dateExpire
     */
    public function setDateExpire(DateTimeInterface $dateExpire): self
    {
        $this->dateExpire = $dateExpire;

        return $this;
    }

    /**
     * Get amount unpaid.
     *
     * @return float
     */
    public function getUnpaid()
    {
        return $this->amount - $this->discount - $this->getAmountPayments();
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmountPayments()
    {
        $amount = 0;
        foreach ($this->payments as $payment) {
            $amount += $payment->getOperation()->getAmount();
        }

        return $amount;
    }

    /**
     * Get amount unpaid.
     *
     * @return string
     */
    public function getStatusPayments()
    {
        return self::getStatusPaymentsStatic($this->getPercentPayments(), $this->period);
    }

    public static function getStatusPaymentsStatic($percentPayment, Period $period) : string
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
     *
     * @return float
     */
    public function getPercentPayments()
    {
        return ($this->getAmountPayments() + $this->discount) / ($this->amount ?: $this->package->getPrice()) * 100;
    }

    /**
     * Get discount.
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discount.
     *
     * @return PackageStudentPeriod
     */
    public function setDiscount(float $discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get paid.
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set paid.
     *
     * @return PackageStudentPeriod
     */
    public function setPaid(bool $paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid.
     *
     * @return bool
     */
    public function getPaidStr()
    {
        return $this->paid ? 'non' : 'oui';
    }

    /**
     * Add payments.
     *
     * @return PackageStudentPeriod
     */
    public function addPayment(PaymentPackageStudent $payments)
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
     *
     * @return Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}

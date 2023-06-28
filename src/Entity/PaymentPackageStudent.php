<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaymentPackageStudentRepository;
use App\Traits\AmountEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PaymentPackageStudentRepository::class)
 */
class PaymentPackageStudent implements \Stringable
{
    use AmountEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity=PackageStudentPeriod::class, cascade={"persist"}, inversedBy="payments")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private PackageStudentPeriod $packageStudentPeriod;

    /**
     * @ORM\ManyToOne(targetEntity=Operation::class, cascade={"persist"}, inversedBy="paymentPackageStudents")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private Operation $operation;

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getAmount(): ?float
    {
        return $this->amount ?: $this->operation->getAmount();
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->operation->getDate();
    }

    public function getAuthor(): ?User
    {
        return $this->operation->getAuthor();
    }

    public function getPackageStudentPeriod(): PackageStudentPeriod
    {
        return $this->packageStudentPeriod;
    }

    public function setPackageStudentPeriod(PackageStudentPeriod $packageStudentPeriod): self
    {
        $this->packageStudentPeriod = $packageStudentPeriod;

        return $this;
    }

    public function getOperation(): Operation
    {
        return $this->operation;
    }

    public function setOperation(Operation $operation): self
    {
        $this->operation = $operation;

        return $this;
    }
}

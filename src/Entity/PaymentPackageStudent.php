<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaymentPackageStudentRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PaymentPackageStudentRepository::class)
 */
class PaymentPackageStudent
{
    use IdEntityTrait;
    use TimestampableEntity;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use AuthorEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=PackageStudentPeriod::class, cascade={"persist"}, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private PackageStudentPeriod $packageStudentPeriod;

    /**
     * @ORM\OneToOne(targetEntity=Operation::class, cascade={"persist"}, inversedBy="paymentPackageStudent")
     * @ORM\JoinColumn(nullable=false)
     */
    private Operation $operation;

    public function __construct()
    {
        $this->enable = true;
    }

    public function getAmount(): ?float
    {
        return $this->operation->getAmount();
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->operation->getDate();
    }

    public function getAuthor(): ?Author
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

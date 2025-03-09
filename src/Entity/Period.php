<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\PeriodRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: PeriodRepository::class)]
class Period implements \Stringable, EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    public function __construct(
        #[ORM\Column(type: Types::DATETIME_MUTABLE)]
        protected ?\DateTimeInterface $begin = new \DateTime(),
        #[ORM\Column(type: Types::DATETIME_MUTABLE)]
        protected ?\DateTimeInterface $end = new \DateTime(),

        /**
         * @var Collection<int, ClassPeriod>
         */
        #[ORM\OneToMany(mappedBy: 'period', targetEntity: ClassPeriod::class)]
        protected Collection $classPeriods = new ArrayCollection()
    ) {}

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTime|\DateTimeImmutable $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTime|\DateTimeImmutable $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function addClassPeriod(ClassPeriod $classPeriod): self
    {
        $this->classPeriods[] = $classPeriod;

        return $this;
    }

    public function removeClassPeriod(ClassPeriod $classPeriod): self
    {
        $this->classPeriods->removeElement($classPeriod);

        return $this;
    }

    public function getClassPeriods(): Collection
    {
        return $this->classPeriods;
    }

    public function getPercent(): float
    {
        $diffNow = $this->begin?->diff(new \DateTime());
        $diffPeriod = $this->begin?->diff($this->end);
        $percent = (int) $diffNow?->format('%R%a') / (int) $diffPeriod?->format('%R%a') * 100;

        return min($percent, 100);
    }
}

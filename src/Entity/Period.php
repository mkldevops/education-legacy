<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PeriodRepository;
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

#[ORM\Entity(repositoryClass: PeriodRepository::class)]
class Period implements \Stringable
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: Diploma::class, inversedBy: 'periods')]
    private ?Diploma $diploma = null;

    public function __construct(
        #[ORM\Column(type: 'datetime')]
        protected ?\DateTimeInterface $begin = new \DateTime(),
        #[ORM\Column(type: 'datetime')]
        protected ?\DateTimeInterface $end = new \DateTime(),
        #[ORM\OneToMany(mappedBy: 'period', targetEntity: ClassPeriod::class)]
        protected Collection $classPeriods = new ArrayCollection()
    ) {
    }

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

    public function addClassPeriod(ClassPeriod $classPeriods): self
    {
        $this->classPeriods[] = $classPeriods;

        return $this;
    }

    public function removeClassPeriod(ClassPeriod $classPeriods): self
    {
        $this->classPeriods->removeElement($classPeriods);

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

    public function getDiploma(): ?Diploma
    {
        return $this->diploma;
    }

    public function setDiploma(?Diploma $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PeriodRepository;
use App\Traits\AuthorEntityTrait;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=PeriodRepository::class)
 */
class Period
{
    use IdEntityTrait;
    use NameEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntityTrait;
    use SoftDeleteableEntity;
    use CommentEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity=ClassPeriod::class, mappedBy="period")
     */
    protected Collection $classPeriods;

    /**
     * @ORM\Column(type="datetime")
     */
    protected ?DateTimeInterface $begin = null;

    /**
     * @ORM\Column(type="datetime")
     */
    protected ?DateTimeInterface $end;

    /**
     * @ORM\ManyToOne(targetEntity=Diploma::class, inversedBy="periods")
     */
    private ?Diploma $diploma;

    public function __construct()
    {
        $this->classPeriods = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBegin(): ?DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @param DateTime|DateTimeImmutable $begin
     */
    public function setBegin(DateTimeInterface $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @param DateTime|DateTimeImmutable $end
     */
    public function setEnd(DateTimeInterface $end): self
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
        $diffNow = $this->begin?->diff(new DateTime());
        $diffPeriod = $this->begin?->diff($this->end);
        $percent = (int) $diffNow?->format('%R%a') / (int) $diffPeriod?->format('%R%a') * 100;

        return $percent > 100 ? 100 : $percent;
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

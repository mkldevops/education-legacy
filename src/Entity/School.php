<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SchoolRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\AddressEntityTrait;
use Fardus\Traits\Symfony\Entity\CityEntityTrait;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Fardus\Traits\Symfony\Entity\ZipEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=SchoolRepository::class)
 */
class School
{
    use AddressEntityTrait;
    use AuthorEntityTrait;
    use CityEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;
    use ZipEntityTrait;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $principal = false;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, cascade={"persist"})
     */
    protected ?Member $director = null;

    /**
     * @ORM\OneToMany(targetEntity=Package::class, mappedBy="school")
     */
    protected Collection $packages;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class, cascade={"persist"})
     */
    protected ?Structure $structure = null;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getPrincipal(): bool
    {
        return $this->principal;
    }

    public function setPrincipal(bool $principal): static
    {
        $this->principal = $principal;

        return $this;
    }

    public function getDirector(): ?Member
    {
        return $this->director;
    }

    public function setDirector(Member $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function addPackage(Package $packages): self
    {
        $this->packages[] = $packages;

        return $this;
    }

    public function removePackage(Package $packages): void
    {
        $this->packages->removeElement($packages);
    }

    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }
}

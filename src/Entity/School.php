<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\SchoolRepository;
use App\Trait\AddressEntityTrait;
use App\Trait\AuthorEntityTrait;
use App\Trait\CityEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\TimestampableEntityTrait;
use App\Trait\ZipEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: SchoolRepository::class)]
class School implements \Stringable, EntityInterface, AuthorEntityInterface
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

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $principal = false;

    #[ORM\ManyToOne(targetEntity: Member::class, cascade: ['persist'])]
    protected ?Member $director = null;

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(mappedBy: 'school', targetEntity: Package::class)]
    protected Collection $packages;

    #[ORM\ManyToOne(targetEntity: Structure::class, cascade: ['persist'])]
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

    public function setDirector(Member $member): self
    {
        $this->director = $member;

        return $this;
    }

    public function addPackage(Package $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    public function removePackage(Package $package): void
    {
        $this->packages->removeElement($package);
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

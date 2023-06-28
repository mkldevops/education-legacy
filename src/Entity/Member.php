<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MemberRepository;
use App\Traits\AuthorEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Fardus\Traits\Symfony\Entity\NameEntityTrait;
use Fardus\Traits\Symfony\Entity\TimestampableEntityTrait;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Table(name: '`person_member`')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member implements \Stringable
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    #[ORM\Column(type: 'string')]
    protected ?string $positionName = null;

    #[ORM\OneToOne(targetEntity: Person::class, cascade: ['persist', 'merge', 'remove'], inversedBy: 'member')]
    protected ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Structure::class, cascade: ['persist'], inversedBy: 'members')]
    protected ?Structure $structure = null;

    public function __toString(): string
    {
        return (string) $this->person;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPositionName(): ?string
    {
        return $this->positionName;
    }

    public function setPositionName(string $positionName): self
    {
        $this->positionName = $positionName;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;
        if ($person instanceof Person) {
            $this->name = $person->getNameComplete();
        }

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): static
    {
        $this->structure = $structure;

        return $this;
    }
}

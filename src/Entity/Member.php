<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\MemberRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\NameEntityTrait;
use App\Trait\TimestampableEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Table(name: '`person_member`')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member implements EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use NameEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING)]
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

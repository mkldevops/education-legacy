<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\StudentCommentRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\StudentEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: StudentCommentRepository::class)]
class StudentComment implements \Stringable, EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use StudentEntityTrait;
    use TimestampableEntity;

    /**
     * @var string
     */
    final public const COMMENT_APPRECIATION = 'success';

    /**
     * @var string
     */
    final public const COMMENT_INFORMATION = 'info';

    /**
     * @var string
     */
    final public const COMMENT_WARNING = 'warning';

    /**
     * @var string
     */
    final public const COMMENT_ALERT = 'danger';

    /**
     * @var null|\DateTime|\DateTimeImmutable
     */
    public ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'comments', cascade: ['persist', 'remove'])]
    protected ?Student $student = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $text;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $type;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return \sprintf('%s %s', (string) $this->id, (string) $this->student?->__toString());
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): static
    {
        $this->student = $student;

        return $this;
    }
}

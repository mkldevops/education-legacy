<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StudentCommentRepository;
use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=StudentCommentRepository::class)
 */
class StudentComment
{
    use IdEntityTrait;
    use StudentEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    public ?\DateTime $created = null;

    public const COMMENT_APPRECIATION = 'success';
    public const COMMENT_INFORMATION = 'info';
    public const COMMENT_WARNING = 'warning';
    public const COMMENT_ALERT = 'danger';

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title;
    /**
     * @ORM\Column(type="text")
     */
    private string $text;
    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $type;

    public function __construct()
    {
        $this->enable = true;
    }

    public function __toString(): string
    {
        return sprintf('%s %s', (string) $this->id, (string) $this->student?->__toString());
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

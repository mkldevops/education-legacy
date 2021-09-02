<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Repository\StudentCommentRepository;

/**
 * @ORM\Entity(repositoryClass=StudentCommentRepository::class)
 */
class StudentComment
{
    public ?\DateTime $created = null;
    use IdEntityTrait;
    use StudentEntityTrait;
    use AuthorEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;

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

    public function getStudent() : ?Student
    {
        return $this->student;
    }

    public function setStudent(Student $student) : static
    {
        $this->student = $student;

        return $this;
    }
}

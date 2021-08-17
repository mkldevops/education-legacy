<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentCommentRepository")
 */
class StudentComment
{
    public ?\DateTime $created = null;
    use IdEntity;
    use StudentEntityTrait;
    use AuthorEntityTrait;
    use EnableEntity;
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

    /**
     * Set text.
     */
    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     *
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     */
    public function setCreated(DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * Get student.
     *
     * @return Student|null
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set student.
     *
     * @return StudentComment
     */
    public function setStudent(Student $student)
    {
        $this->student = $student;

        return $this;
    }
}

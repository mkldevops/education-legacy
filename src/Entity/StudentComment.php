<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\AuthorEntityTrait;
use App\Traits\StudentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentCommentRepository")
 */
class StudentComment
{
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
     *
     * @return StudentComment
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return StudentComment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return StudentComment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get student.
     *
     * @return Student
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

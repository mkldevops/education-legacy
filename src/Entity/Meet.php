<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MeetRepository;
use App\Traits\AuthorEntityTrait;
use App\Traits\PublisherEntityTrait;
use App\Traits\SchoolEntityTrait;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Fardus\Traits\Symfony\Entity\EnableEntity;
use Fardus\Traits\Symfony\Entity\IdEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=MeetRepository::class)
 */
class Meet
{
    use IdEntity;
    use AuthorEntityTrait;
    use EnableEntity;
    use TimestampableEntity;
    use SchoolEntityTrait;
    use PublisherEntityTrait;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $subject;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $text;

    public function __toString(): string
    {
        return (string)$this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }
}

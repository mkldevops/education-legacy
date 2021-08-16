<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Manager\CourseManager;
use App\Repository\AppealCourseRepository;
use App\Traits\StudentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Fardus\Traits\Symfony\Entity\CommentEntityTrait;
use Fardus\Traits\Symfony\Entity\EnableEntityTrait;
use Fardus\Traits\Symfony\Entity\IdEntityTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=AppealCourseRepository::class)
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(columns={"student_id", "course_id"})})
 */
class AppealCourse
{
    use IdEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use TimestampableEntity;
    use StudentEntityTrait;

    public const STATUS_NOTHING = 0;
    public const STATUS_PRESENT = 1;
    public const STATUS_ABSENT = 2;
    public const STATUS_ABSENT_JUSTIFIED = 3;
    public const STATUS_LAG = 4;
    public const STATUS_LAG_UNACCEPTED = 5;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, cascade={"persist", "remove"}, inversedBy="students")
     */
    private Course $course;

    /**
     * @ORM\Column(type="integer")
     */
    private int $status = self::STATUS_NOTHING;

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @throws AppException
     * @return mixed[]
     */
    public function getInfoStatus(): array
    {
        return CourseManager::getListStatus($this->status);
    }
}

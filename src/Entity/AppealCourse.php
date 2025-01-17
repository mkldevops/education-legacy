<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AppException;
use App\Manager\CourseManager;
use App\Repository\AppealCourseRepository;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use App\Trait\StudentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table]
#[UniqueConstraint(columns: ['student_id', 'course_id'])]
#[ORM\Entity(repositoryClass: AppealCourseRepository::class)]
class AppealCourse implements \Stringable
{
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use StudentEntityTrait;
    use TimestampableEntity;

    /**
     * @var int
     */
    final public const STATUS_NOTHING = 0;

    /**
     * @var int
     */
    final public const STATUS_PRESENT = 1;

    /**
     * @var int
     */
    final public const STATUS_ABSENT = 2;

    /**
     * @var int
     */
    final public const STATUS_ABSENT_JUSTIFIED = 3;

    /**
     * @var int
     */
    final public const STATUS_LAG = 4;

    /**
     * @var int
     */
    final public const STATUS_LAG_UNACCEPTED = 5;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'appealCourses', cascade: ['persist', 'remove'])]
    protected ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Course::class, cascade: ['persist', 'remove'], inversedBy: 'students')]
    private Course $course;

    #[ORM\Column(type: 'integer')]
    private int $status = self::STATUS_NOTHING;

    public function __toString(): string
    {
        return sprintf('%s %s', $this->course->__toString(), $this->student->__toString());
    }

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
     * @return array[]
     *
     * @throws AppException
     */
    public function getInfoStatus(): array
    {
        return CourseManager::getListStatus($this->status);
    }
}

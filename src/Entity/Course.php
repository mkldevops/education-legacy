<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\AuthorEntityInterface;
use App\Entity\Interface\EntityInterface;
use App\Repository\CourseRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course implements EntityInterface, AuthorEntityInterface
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use TimestampableEntity;

    /**
     * @var Collection<int, AppealCourse>
     */
    #[ORM\OneToMany(targetEntity: AppealCourse::class, mappedBy: 'course')]
    protected Collection $students;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: true, options: ['default' => null])]
    private string $idEvent;

    #[ORM\ManyToOne(targetEntity: ClassPeriod::class, inversedBy: 'courses', cascade: ['persist'])]
    private ClassPeriod $classPeriod;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $date;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTimeInterface $hourBegin;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTimeInterface $hourEnd;

    /**
     * @var Collection<int, Teacher>
     */
    #[ORM\ManyToMany(targetEntity: Teacher::class, mappedBy: 'courses', cascade: ['persist'])]
    private Collection $teachers;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->setStudents(new ArrayCollection())
            ->setTeachers(new ArrayCollection())
            ->setDate(new \DateTime())
        ;
    }

    public function __toString(): string
    {
        return $this->getNameComplete();
    }

    public function getNameComplete(): string
    {
        return \sprintf(
            '%s [%s, %s - %s]',
            $this->classPeriod->getName(),
            $this->date->format('d/m/Y'),
            $this->hourBegin->format('H:i'),
            $this->hourEnd->format('H:i')
        );
    }

    public function getClassPeriod(): ClassPeriod
    {
        return $this->classPeriod;
    }

    public function setClassPeriod(ClassPeriod $classPeriod): self
    {
        $this->classPeriod = $classPeriod;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getHourBegin(): \DateTimeInterface
    {
        return $this->hourBegin;
    }

    public function setHourBegin(\DateTimeInterface $hourBegin): static
    {
        $this->hourBegin = $hourBegin;

        return $this;
    }

    public function getHourEnd(): \DateTimeInterface
    {
        return $this->hourEnd;
    }

    public function setHourEnd(\DateTimeInterface $hourEnd): static
    {
        $this->hourEnd = $hourEnd;

        return $this;
    }

    public function addTeacher(Teacher $teacher): static
    {
        if (!$this->teachers->contains($teacher)) {
            $this->teachers[] = $teacher;
        }

        return $this;
    }

    public function getTeachersToString(): string
    {
        $str = '';
        foreach ($this->teachers as $teacher) {
            $str .= ('' === $str || '0' === $str ? '' : ', ').$teacher;
        }

        return $str;
    }

    /**
     * @return Collection<int, Teacher>
     */
    public function getTeachers(): Collection|iterable
    {
        return $this->teachers;
    }

    /**
     * @param Collection|Teacher[] $teachers
     */
    public function setTeachers(array|Collection $teachers): self
    {
        $this->teachers = $teachers;

        return $this;
    }

    public function addStudent(AppealCourse $appealCourse): self
    {
        $this->students[] = $appealCourse;

        return $this;
    }

    public function removeStudent(AppealCourse $appealCourse): self
    {
        $this->students->removeElement($appealCourse);

        return $this;
    }

    /**
     * @return Collection<int, AppealCourse>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    /**
     * @param AppealCourse[]|Collection $students
     */
    public function setStudents(array|Collection $students): self
    {
        $this->students = $students;

        return $this;
    }

    public function getIdEvent(): string
    {
        return $this->idEvent;
    }

    public function setIdEvent(string $idEvent): self
    {
        $this->idEvent = $idEvent;

        return $this;
    }
}

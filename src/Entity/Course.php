<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourseRepository;
use App\Trait\AuthorEntityTrait;
use App\Trait\CommentEntityTrait;
use App\Trait\EnableEntityTrait;
use App\Trait\IdEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course implements \Stringable
{
    use AuthorEntityTrait;
    use CommentEntityTrait;
    use EnableEntityTrait;
    use IdEntityTrait;
    use TimestampableEntity;

    #[ORM\OneToMany(targetEntity: AppealCourse::class, mappedBy: 'course')]
    protected Collection $students;

    #[ORM\Column(type: 'string', unique: true, nullable: true, options: ['default' => null])]
    private string $idEvent;

    #[ORM\ManyToOne(targetEntity: ClassPeriod::class, inversedBy: 'courses', cascade: ['persist'])]
    private ClassPeriod $classPeriod;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $hourBegin;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $hourEnd;

    #[ORM\ManyToMany(targetEntity: Teacher::class, mappedBy: 'courses', cascade: ['persist'])]
    private Collection $teachers;

    public function __construct()
    {
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
        return sprintf(
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
            $str .= (empty($str) ? '' : ', ').$teacher;
        }

        return $str;
    }

    /**
     * @return Collection|Teacher[]
     */
    public function getTeachers(): iterable|Collection
    {
        return $this->teachers;
    }

    /**
     * @param Collection|Teacher[] $teachers
     */
    public function setTeachers(Collection|array $teachers): self
    {
        $this->teachers = $teachers;

        return $this;
    }

    public function addStudent(AppealCourse $students): self
    {
        $this->students[] = $students;

        return $this;
    }

    public function removeStudent(AppealCourse $students): self
    {
        $this->students->removeElement($students);

        return $this;
    }

    /**
     * @return AppealCourse[]|Collection
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

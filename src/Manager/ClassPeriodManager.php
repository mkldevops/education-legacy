<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ClassPeriod;
use App\Entity\ClassPeriodStudent;
use App\Entity\Course;
use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\School;
use App\Entity\Student;
use App\Services\AbstractFullService;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;

class ClassPeriodManager extends AbstractFullService
{
    public function findClassPeriod($name, Period $period, School $school): ?ClassPeriod
    {
        $classPeriod = null;

        try {
            $classPeriod = $this->getEntityManager()
                ->getRepository(ClassPeriod::class)
                ->getClassPeriodByName($name, $period, $school);

            $this->logger->debug(__FUNCTION__, ['classPeriod' => $classPeriod]);
        } catch (NonUniqueResultException $e) {
            $this->logger->error('Not found class period with name : ' . $name, ['name' => $name, 'period' => $period, 'school' => $school]);
        }

        return $classPeriod;
    }

    /**
     * Get Package Student.
     *
     * @return PackageStudentPeriod[]
     *
     * @throws NonUniqueResultException
     */
    public function getPackageStudent(ClassPeriod $classPeriod): array
    {
        $studentPeriods = $classPeriod->getStudents();
        $packageStudents = [];

        foreach ($studentPeriods as $studentPeriod) {
            $id = $studentPeriod->getStudent()->getId();
            $package = $this->getEntityManager()
                ->getRepository(PackageStudentPeriod::class)
                ->getCurrentPackageStudent($id, $classPeriod->getPeriod());

            if (!empty($package)) {
                $packageStudents[$id] = $package;
            }
        }

        return $packageStudents;
    }

    /**
     * Get Students In ClassPeriod.
     *
     * @return array|ClassPeriodStudent[]
     */
    public function getStudentsInClassPeriod(ClassPeriod $classPeriod): array
    {
        return $this->getEntityManager()
            ->getRepository(ClassPeriod::class)
            ->getStudentToClassPeriod($classPeriod);
    }

    /**
     * @param DateTime|DateTimeImmutable $from
     * @return array|Course[]
     *
     */
    public function getCourses(ClassPeriod $classPeriod, int $page, int $maxResult, DateTimeInterface $from): array
    {
        $offset = ($page - 1) * $maxResult;
        $courses = $this->getEntityManager()
            ->getRepository(Course::class)
            ->getCourseOfClass($classPeriod, $from, $maxResult, $offset);

        if (empty($courses)) {
            $courses = array_fill(0, 17, []);
        }

        $this->logger->debug(__FUNCTION__ . ' length courses : ' . count($courses));

        return $courses;
    }

    /**
     * @param DateTime|DateTimeImmutable $from
     */
    public function getNbCourses(ClassPeriod $classPeriod, DateTimeInterface $from): ?int
    {
        $courses = null;
        try {
            $courses = (int)$this->getEntityManager()
                ->getRepository(Course::class)
                ->createQueryBuilder('c')
                ->select('count(c.id)')
                ->where('c.classPeriod = :classPeriod')
                ->setParameter('classPeriod', $classPeriod)
                ->andWhere('c.date >= :date')
                ->setParameter('date', $from)
                ->getQuery()
                ->getSingleScalarResult();
            $this->logger->debug(__FUNCTION__ . ' length total courses : ' . $courses);
        } catch (NonUniqueResultException | NoResultException $e) {
            $this->logger->error($e->getMessage());
        }

        return $courses;
    }

    /**
     * Treatment to add List Student.
     *
     *
     * @throws ORMException
     * @internal param array $students
     */
    public function treatListStudent(array $studentsId, Period $period, ClassPeriod $classPeriod = null): bool
    {
        if ($classPeriod instanceof ClassPeriod && $period->getId() !== $classPeriod->getPeriod()->getId()) {
            throw new Exception('The current period is not availbale for update classPeriod');
        }

        if (!empty($studentsId)) {
            $students = $this->entityManager
                ->getRepository(Student::class)
                ->findBy(['id' => $studentsId]);

            if (empty($students)) {
                throw new Exception('Not found student id : ' . implode(',', $studentsId));
            }

            foreach ($students as $student) {
                $currentClassPeriodStudent = $this->entityManager
                    ->getRepository(ClassPeriodStudent::class)
                    ->getCurrentClassPeriodStudent($student, $period);

                if ($currentClassPeriodStudent instanceof ClassPeriodStudent) {
                    $end = $currentClassPeriodStudent->getClassPeriod()->getPeriod()->getEnd();
                    if ($end->getTimestamp() > time()) {
                        $end = new DateTime();
                    }

                    $currentClassPeriodStudent->setEnd($end)
                        ->setEnable(false)
                        ->setAuthor($this->getUser())
                        ->setComment('Change for new class ' . $classPeriod->getClassSchool()->getName());
                } else {
                    $this->logger->debug("don't have a current ClassPeriodStudent", compact($student));
                }

                if ($classPeriod instanceof ClassPeriod) {
                    $this->persistClassPeriodStudent($classPeriod, $student);
                }
            }

            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * persist Class Period Student.
     *
     *
     * @throws ORMException
     * @throws Exception
     */
    private function persistClassPeriodStudent(ClassPeriod $classPeriod, Student $student): bool
    {
        $classPeriodStudent = new ClassPeriodStudent();

        $begin = new DateTime();

        // On vérifie si la date debut de la periode n'est pas encore passé
        if ($classPeriod->getPeriod()->getBegin()->getTimestamp() > time()) {
            $begin = $classPeriod->getPeriod()->getBegin();
        }

        $classPeriodStudent->setClassPeriod($classPeriod)
            ->setBegin($begin)
            ->setEnable(true)
            ->setEnd($classPeriod->getPeriod()->getEnd())
            ->setStudent($student)
            ->setAuthor($this->getUser());

        $this->getEntityManager()
            ->persist($classPeriodStudent);

        return true;
    }

    /**
     * Get List Student Without.
     *
     *
     * @throws Exception
     * @return array<int, array<string, mixed>>
     */
    public function getListStudentWithout(Period $period, School $school): array
    {
        $result = $this->getEntityManager()
            ->getRepository(Student::class)
            ->getListStudentsWithoutClassPeriod($period, $school);

        $students = [];
        foreach ($result as $student) {
            /** @var DateTime $birthday */
            $birthday = $student['birthday'];

            /** @var DateTime $registration */
            $registration = $student['dateRegistration'];

            $students[] = [
                'DT_RowId' => 'student_' . $student['id'],
                'DT_RowData' => ['id' => $student['id']],
                'id' => $student['id'],
                'name' => $student['name'],
                'forname' => $student['forname'],
                'age' => $birthday->diff(new DateTime())->y,
                'gender' => $student['gender'],
                'dateRegistration' => $registration->format('d/m/Y'),
            ];
        }

        return $students;
    }

    /**
     * getListOfCurrentPeriod.
     *
     * @return ClassPeriod[]
     */
    public function getListOfCurrentPeriod(Period $period, School $school)
    {
        return $this->getQueryBuilderList($school)
            ->andWhere('cp.period = :period')
            ->setParameter(':period', $period)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Query Builder List.
     *
     *
     *
     * @internal param School|null $school
     */
    private function getQueryBuilderList(School $school, string $search = ''): QueryBuilder
    {
        return $this->getEntityManager()
            ->getRepository(ClassPeriod::class)
            ->createQueryBuilder('cp')
            ->innerJoin('cp.classSchool', 'cs', Join::WITH, 'cs.school = :school')
            ->setParameter(':school', $school)
            ->where('cp.comment LIKE :comment')
            ->setParameter(':comment', '%' . $search . '%')
            ->orWhere('cp.enable LIKE :status')
            ->setParameter(':status', '%' . $search . '%');
    }

    /**
     * @throws NonUniqueResultException
     */
    public function count(School $school, string $search): int
    {
        return (int)$this->getQueryBuilderList($school, $search)
            ->select('count(cp.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $page
     *
     * @return mixed
     */
    public function getList(School $school, int $page, string $search)
    {
        return $this->getQueryBuilderList($school, $search)
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}

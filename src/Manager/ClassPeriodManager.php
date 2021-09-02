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
use App\Exception\AppException;
use App\Manager\Interfaces\ClassPeriodManagerInterface;
use App\Repository\ClassPeriodRepository;
use App\Repository\ClassPeriodStudentRepository;
use App\Repository\CourseRepository;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\StudentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class ClassPeriodManager implements ClassPeriodManagerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ClassPeriodRepository $classPeriodRepository,
        private ClassPeriodStudentRepository $classPeriodStudentRepository,
        private CourseRepository $courseRepository,
        private StudentRepository $studentRepository,
        private PackageStudentPeriodRepository $packageStudentPeriodRepository,
        private Security $security,
    ) {
    }

    /**
     * @throws AppException
     */
    public function findClassPeriod(string $name, Period $period, School $school): ?ClassPeriod
    {
        try {
            $classPeriod = $this->classPeriodRepository->getClassPeriodByName($name, $period, $school);
            $this->logger->debug(__FUNCTION__, ['classPeriod' => $classPeriod]);

            return $classPeriod;
        } catch (NonUniqueResultException $e) {
            $msg = 'Not found class period with name : '.$name;
            $this->logger->error($msg, compact('name', 'period', 'school'));
            throw new AppException($msg, (int) $e->getCode(), $e);
        }
    }

    /**
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
            $package = $this->packageStudentPeriodRepository->getCurrentPackageStudent($id, $classPeriod->getPeriod());

            if (null !== $package) {
                $packageStudents[$id] = $package;
            }
        }

        return $packageStudents;
    }

    /**
     * @return ClassPeriodStudent[]
     *
     * @throws Exception
     */
    public function getStudentsInClassPeriod(ClassPeriod $classPeriod): array
    {
        return $this->classPeriodRepository->getStudentToClassPeriod($classPeriod);
    }

    /**
     * @return Course[]
     */
    public function getCourses(ClassPeriod $classPeriod, int $page, int $maxResult, DateTimeInterface $from): array
    {
        $offset = ($page - 1) * $maxResult;
        $courses = $this->courseRepository->getCourseOfClass($classPeriod, $from, $maxResult, $offset);

        if (empty($courses)) {
            $courses = array_fill(0, 17, []);
        }

        $this->logger->debug(__FUNCTION__.' length courses : '.count($courses));

        return $courses;
    }

    public function getNbCourses(ClassPeriod $classPeriod, DateTimeInterface $from): ?int
    {
        $courses = null;
        try {
            $courses = (int) $this->courseRepository
                ->createQueryBuilder('c')
                ->select('count(c.id)')
                ->where('c.classPeriod = :classPeriod')
                ->setParameter('classPeriod', $classPeriod)
                ->andWhere('c.date >= :date')
                ->setParameter('date', $from)
                ->getQuery()
                ->getSingleScalarResult();
            $this->logger->debug(__FUNCTION__.' length total courses : '.$courses);
        } catch (NonUniqueResultException | NoResultException $e) {
            $this->logger->error($e->getMessage());
        }

        return $courses;
    }

    /**
     * @throws ORMException
     * @throws AppException
     *
     * @internal param array $students
     */
    public function treatListStudent(array $studentsId, Period $period, ClassPeriod $classPeriod): bool
    {
        if ($period->getId() !== $classPeriod->getPeriod()->getId()) {
            throw new AppException('The current period is not available for update classPeriod');
        }

        if (!empty($studentsId)) {
            $students = $this->studentRepository->findBy(['id' => $studentsId]);

            if (empty($students)) {
                throw new AppException('Not found student id : '.implode(',', $studentsId));
            }

            foreach ($students as $student) {
                $classPeriodStudent = $this->classPeriodStudentRepository
                    ->getCurrentClassPeriodStudent($student, $period);

                if ($classPeriodStudent instanceof ClassPeriodStudent) {
                    $end = $classPeriodStudent->getClassPeriod()?->getPeriod()?->getEnd();
                    if (null === $end || $end->getTimestamp() > time()) {
                        $end = new DateTime();
                    }

                    $classPeriodStudent->setEnd($end)
                        ->setEnable(false)
                        ->setAuthor($this->security->getUser())
                        ->setComment('Change for new class '.$classPeriod->getClassSchool()->getName());
                } else {
                    $this->logger->debug("don't have a current ClassPeriodStudent", compact('student'));
                }

                $this->persistClassPeriodStudent($classPeriod, $student);
            }

            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @throws ORMException
     * @throws Exception
     */
    private function persistClassPeriodStudent(ClassPeriod $classPeriod, Student $student): void
    {
        $classPeriodStudent = new ClassPeriodStudent();

        $begin = new DateTime();

        // On vérifie si la date debut de la periode n'est pas encore passé
        $timestamp = $classPeriod->getPeriod()->getBegin()?->getTimestamp();
        if (null !== $timestamp && $timestamp > time()) {
            $begin = $classPeriod->getPeriod()->getBegin();
        }

        $classPeriodStudent->setClassPeriod($classPeriod)
            ->setBegin($begin)
            ->setEnable(true)
            ->setEnd($classPeriod->getPeriod()->getEnd())
            ->setStudent($student)
            ->setAuthor($this->security->getUser());

        $this->entityManager->persist($classPeriodStudent);
    }

    /**
     * @throws Exception
     *
     * @return array<int, array<string, mixed>>
     */
    public function getListStudentWithout(Period $period, School $school): array
    {
        $result = $this->studentRepository->getListStudentsWithoutClassPeriod($period, $school);

        $students = [];
        foreach ($result as $student) {
            /** @var DateTime $birthday */
            $birthday = $student['birthday'];

            /** @var DateTime $registration */
            $registration = $student['dateRegistration'];

            $students[] = [
                'DT_RowId' => 'student_'.$student['id'],
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
}

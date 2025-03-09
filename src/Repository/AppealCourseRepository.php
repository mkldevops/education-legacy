<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AppealCourse;
use App\Entity\ClassPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppealCourse>
 */
class AppealCourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, AppealCourse::class);
    }

    /**
     * Get list Students.
     */
    public function getAppealToClassPeriod(ClassPeriod $classPeriod, array $listStatus): object
    {
        $oQuery = $this->createQueryBuilder('apc')
            ->select('apc')
            ->addSelect('cou')
            ->addSelect('std')
            ->addSelect('prs')
            ->innerJoin('apc.student', 'std')
            ->innerJoin('std.person', 'prs')
            ->innerJoin('apc.course', 'cou')
            ->innerJoin('cou.classPeriod', 'clp')
            ->where('cou.classPeriod = :classperiod')
            ->orderBy('cou.date', 'ASC')
            ->setParameter('classperiod', $classPeriod->getId())
            ->getQuery()
        ;

        $appeals = new class {
            /** @var array<object> */
            public array $courses = [];

            /** @var array<object> */
            public array $students = [];
        };

        $data = $oQuery->getArrayResult();

        foreach ($listStatus as $key => $status) {
            $status['count'] = 0;

            $listStatus[$key] = $status;
        }

        foreach ($data as $value) {
            $courseId = (int) $value['course']['id'];
            $studentId = (int) $value['student']['id'];
            $statusId = $value['status'];

            $value['student']['nameComplete'] = strtoupper((string) $value['student']['person']['name']).' '.ucfirst((string) $value['student']['person']['forname']);
            $value['course']['dateStr'] = $value['course']['date']->format('d/m');
            $value['status'] = $listStatus[$value['status']];

            if (!\array_key_exists($courseId, $appeals->courses)) {
                $appeals->courses[$courseId] = new class {
                    public ?array $course = null;

                    /** @var array<object> */
                    public array $students = [];
                };
            }

            if (!\array_key_exists($studentId, $appeals->students)) {
                $appeals->students[$studentId] = $value['student'];
                $appeals->students[$studentId]['listStatus'] = $listStatus;
                $appeals->students[$studentId]['countCOurse'] = 0;
            }

            $appeals->courses[$courseId]->students[$studentId] = $value;
            ++$appeals->students[$studentId]['countCOurse'];
            ++$appeals->students[$studentId]['listStatus'][$statusId]['count'];
        }

        return $appeals;
    }
}

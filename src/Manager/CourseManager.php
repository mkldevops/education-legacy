<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 10/08/18
 * Time: 14:40.
 */

namespace App\Manager;

use App\Entity\AppealCourse;
use App\Entity\ClassPeriod;
use App\Entity\Course;
use App\Entity\Period;
use App\Entity\School;
use App\Exception\AppException;
use App\Services\AbstractFullService;
use App\Services\GoogleCalendarService;
use DateTime;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Description of class CourseManager.
 *
 * @author  fahari
 */
class CourseManager extends AbstractFullService
{
    private GoogleCalendarService $googleCalendar;

    private ClassPeriodManager $classPeriodManager;

    /**
     * @throws AppException
     */
    public static function getListStatus($status = null): array
    {
        $list = [
            AppealCourse::STATUS_NOTHING => [
                'id' => AppealCourse::STATUS_NOTHING,
                'label' => '-',
                'short' => '-',
                'class' => 'default',
            ],
            AppealCourse::STATUS_PRESENT => [
                'id' => AppealCourse::STATUS_PRESENT,
                'label' => 'présent',
                'short' => 'P',
                'class' => 'success',
            ],
            AppealCourse::STATUS_ABSENT => [
                'id' => AppealCourse::STATUS_ABSENT,
                'label' => 'absent',
                'short' => 'A',
                'class' => 'danger',
            ],
            AppealCourse::STATUS_ABSENT_JUSTIFIED => [
                'id' => AppealCourse::STATUS_ABSENT_JUSTIFIED,
                'label' => 'absence justifié',
                'short' => 'AJ',
                'class' => 'warning',
            ],
            AppealCourse::STATUS_LAG => [
                'id' => AppealCourse::STATUS_LAG,
                'label' => 'retard',
                'short' => 'R',
                'class' => 'warning',
            ],
            AppealCourse::STATUS_LAG_UNACCEPTED => [
                'id' => AppealCourse::STATUS_LAG_UNACCEPTED,
                'label' => 'retard non accepté',
                'short' => 'RN',
                'class' => 'danger',
            ],
        ];

        if (!empty($status)) {
            if (!isset($list[$status])) {
                throw new AppException('not found status : '.$status);
            }

            return $list[$status];
        }

        return $list;
    }

    /**
     * @throws AppException
     */
    public function generate(Period $period, School $school): int
    {
        $courseEvents = $this->googleCalendar
            ->setTimeMin($period->getBegin())
            ->setTimeMax($period->getEnd())
            ->getEvents('Cours');

        $this->logger->debug(__METHOD__, ['courseEvents' => $courseEvents]);

        foreach ($courseEvents as $courseEvent) {
            $course = $this->getEntityManager()
                ->getRepository(Course::class)
                ->findOneBy(['idEvent' => $courseEvent->getId()]);

            if (!$course instanceof Course) {
                $this->logger->debug(__FUNCTION__.' new instance of course', ['course' => $course]);
                $course = new Course();
            }

            $name = preg_replace("/(\w+) (\w+)( .*+)?/", '$2', $courseEvent->getSummary());
            $classPeriod = $this->classPeriodManager->findClassPeriod($name, $period, $school);

            if (!$classPeriod instanceof ClassPeriod) {
                $this->logger->warning(__FUNCTION__.' Not found class period : '.$name);
                unset($course);

                continue;
            }

            $begin = new DateTime($courseEvent->getStart()->getDateTime());
            $end = new DateTime($courseEvent->getEnd()->getDateTime());

            $course
                ->setIdEvent($courseEvent->getId())
                ->setClassPeriod($classPeriod)
                ->setText($courseEvent->getSummary())
                ->setComment($courseEvent->getDescription())
                ->setEnable('confirmed' === $courseEvent->getStatus())
                ->setDate($begin)
                ->setHourBegin($begin)
                ->setHourEnd($end)
                ->setCreatedAt(new DateTime($courseEvent->getCreated()))
                ->setAuthor($this->getUser());

            $this->getEntityManager()->persist($course);
            $this->getEntityManager()->flush();
        }

        return count($courseEvents);
    }

    public function getGoogleCalendar(): GoogleCalendarService
    {
        return $this->googleCalendar;
    }

    #[Required]
    public function setGoogleCalendar(GoogleCalendarService $googleCalendar): self
    {
        $this->googleCalendar = $googleCalendar;

        return $this;
    }

    public function getClassPeriodManager(): ClassPeriodManager
    {
        return $this->classPeriodManager;
    }

    #[Required]
    public function setClassPeriodManager(ClassPeriodManager $classPeriodManager): self
    {
        $this->classPeriodManager = $classPeriodManager;

        return $this;
    }
}

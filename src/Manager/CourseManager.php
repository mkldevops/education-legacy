<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\AppealCourse;
use App\Exception\AppException;

final readonly class CourseManager
{
    /**
     * @throws AppException
     */
    public static function getListStatus(?int $status = null): array
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

        if (null !== $status && 0 !== $status) {
            if (!isset($list[$status])) {
                throw new AppException('not found status : '.$status);
            }

            return $list[$status];
        }

        return $list;
    }
}

<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Operation;
use App\Entity\Period;
use App\Entity\School;
use App\Model\DataStats;
use App\Model\StatsByMonth;

/**
 * Created by PhpStorm.
 * User: fardus
 * Date: 13/05/2016
 * Time: 22:13
 * PHP version : 7.1.
 *
 * @author fardus <h.fahari@gmail.com>
 */
class StatisticsManager extends AccountableManager
{
    /**
     * Get Stats By Month.
     *
     * @return StatsByMonth
     */
    public function getStatsByMonth(Period $period, School $school)
    {
        $this->logger->debug(__FUNCTION__, ['period' => $period, 'school' => $school]);

        $operations = $this->findOperationOfStats($period, $school);
        $stats = new StatsByMonth();

        foreach ($operations as $operation) {
            try {
                $dataStats = (new DataStats())
                    ->setRowId($operation['idTypeOperation'])
                    ->setRowLabel($operation['nameTypeOperation'])
                    ->setColumnLabel((new \DateTime($operation['groupDate']))->format('M Y'))
                    ->setColumnId($operation['groupDate'])
                    ->setCount((int) $operation['numberOperations'])
                    ->setSum((float) $operation['sumCredit'] + (float) $operation['sumDebit']);

                $stats->addData($dataStats);
            } catch (\Exception $e) {
                $this->logger->error(__METHOD__.' '.$e->getMessage(), compact($operation));
            }
        }

        return $stats;
    }

    /**
     * findOperationofStats.
     *
     * @return array
     */
    private function findOperationOfStats(Period $period, School $school)
    {
        return $this
            ->getEntityManager()
            ->getRepository(Operation::class)
            ->getStatsByMonthly($period, $school);
    }
}

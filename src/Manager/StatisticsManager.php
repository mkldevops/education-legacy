<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\AppException;
use App\Model\DataStats;
use App\Model\StatsByMonth;
use App\Repository\OperationRepository;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;

class StatisticsManager
{
    public function __construct(
        private LoggerInterface $logger,
        private OperationRepository $repository,
        private PeriodManager $periodManager,
        private SchoolManager $schoolManager,
    ) {
    }

    /**
     * @throws AppException
     */
    public function getStatsByMonth(): StatsByMonth
    {
        $period = $this->periodManager->getPeriodsOnSession();
        $school = $this->schoolManager->getSchool();
        $this->logger->debug(__FUNCTION__, ['period' => $period, 'school' => $school]);

        $operations = $this->repository->getStatsByMonthly($period, $school);
        $stats = new StatsByMonth();

        foreach ($operations as $operation) {
            try {
                $dataStats = (new DataStats())
                    ->setRowId($operation['idTypeOperation'])
                    ->setRowLabel($operation['nameTypeOperation'])
                    ->setColumnLabel((new DateTime($operation['groupDate']))->format('M Y'))
                    ->setColumnId($operation['groupDate'])
                    ->setCount((int) $operation['numberOperations'])
                    ->setSum((float) $operation['sumCredit'] + (float) $operation['sumDebit']);

                $stats->addData($dataStats);
            } catch (Exception $e) {
                $this->logger->error(__METHOD__.' '.$e->getMessage(), compact($operation));
                throw new AppException($e->getMessage(), (int) $e->getCode(), $e);
            }
        }

        return $stats;
    }
}

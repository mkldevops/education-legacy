<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\AppException;
use App\Model\DataStats;
use App\Model\StatsByMonth;
use App\Repository\OperationRepository;
use Psr\Log\LoggerInterface;

class StatisticsManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly OperationRepository $operationRepository,
        private readonly PeriodManager $periodManager,
        private readonly SchoolManager $schoolManager,
    ) {}

    /**
     * @throws AppException
     */
    public function getStatsByMonth(): StatsByMonth
    {
        $period = $this->periodManager->getPeriodsOnSession();
        $school = $this->schoolManager->getSchool();
        $this->logger->debug(__FUNCTION__, ['period' => $period, 'school' => $school]);

        $operations = $this->operationRepository->getStatsByMonthly($period, $school);
        $statsByMonth = new StatsByMonth();

        foreach ($operations as $operation) {
            try {
                $dataStats = (new DataStats())
                    ->setRowId($operation['idTypeOperation'])
                    ->setRowLabel($operation['nameTypeOperation'])
                    ->setColumnLabel((new \DateTime($operation['groupDate']))->format('M Y'))
                    ->setColumnId($operation['groupDate'])
                    ->setCount((int) $operation['numberOperations'])
                    ->setSum((float) $operation['sumCredit'] + (float) $operation['sumDebit'])
                ;

                $statsByMonth->addData($dataStats);
            } catch (\Exception $exception) {
                $this->logger->error(__METHOD__.' '.$exception->getMessage(), compact($operation));

                throw new AppException($exception->getMessage(), (int) $exception->getCode(), $exception);
            }
        }

        return $statsByMonth;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use Exception;

class StatsByMonth
{
    /**
     * @var DataStats[]
     */
    private array $column = [];

    /**
     * @var DataStats[]
     */
    private array $row = [];
    private DataStats $global;
    private array $data = [];

    public function __construct()
    {
        $this->setGlobal(new DataStats());
    }

    public function addColumn(DataStats $data): self
    {
        if (!isset($this->column[$data->getColumnId()])) {
            $this->column[$data->getColumnId()] = (new DataStats())
                ->setColumnId($data->getColumnId())
                ->setColumnLabel($data->getColumnLabel())
                ->setSum(0)
                ->setCount(0)
            ;
        }

        $this->column[$data->getColumnId()]->merge($data);

        ksort($this->column);

        return $this;
    }

    public function setColumn(array $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return DataStats[]
     */
    public function getColumn(): array
    {
        return $this->column;
    }

    public function setRow(array $row = []): StatsByMonth
    {
        $this->row = $row;

        return $this;
    }

    public function addRow(DataStats $dataStats): StatsByMonth
    {
        if (!$rowId = $dataStats->getRowId()) {
            return $this;
        }

        if (!isset($this->row[$rowId])) {
            $this->row[$rowId] = (new DataStats())
                ->setRowId($rowId)
                ->setRowLabel($dataStats->getRowLabel())
                ->setCount(0)
                ->setSum(0)
            ;
        }

        $this->row[$rowId]->merge($dataStats);

        ksort($this->row, SORT_ASC);

        return $this;
    }

    /**
     * @return DataStats[]
     */
    public function getRow(): array
    {
        return $this->row;
    }

    public function setData(array $data): StatsByMonth
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addData(DataStats $dataStats): StatsByMonth
    {
        if (!isset($this->data[$dataStats->getRowId()])) {
            $this->data[$dataStats->getRowId()] = [];
        }

        if (isset($this->data[$dataStats->getRowId()][$dataStats->getColumnId()])) {
            throw new Exception('data exists');
        }

        $this->addRow($dataStats)
            ->addColumn($dataStats)
            ->addGlobal($dataStats);

        $this->data[$dataStats->getRowId()][$dataStats->getColumnId()] = $dataStats;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setGlobal(DataStats $global): StatsByMonth
    {
        $this->global = $global;

        return $this;
    }

    public function getGlobal(): DataStats
    {
        return $this->global;
    }

    private function addGlobal(DataStats $dataStats): self
    {
        $this->getGlobal()->merge($dataStats);

        return $this;
    }
}

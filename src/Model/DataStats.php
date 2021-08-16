<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: fahari
 * Date: 31/12/18
 * Time: 15:32.
 */

namespace App\Model;

class DataStats
{
    private ?string $columnId = null;

    private ?string $columnLabel = null;

    private null|int|string $rowId = null;

    private ?string $rowLabel = null;

    private ?int $count = null;

    private ?float $sum = null;

    /**
     * DataStats constructor.
     */
    public function __construct()
    {
        $this->setSum(0)
            ->setCount(0);
    }

    /**
     * @return string
     */
    public function getColumnLabel(): ?string
    {
        return $this->columnLabel;
    }

    public function setColumnLabel(string $columnLabel = null): static
    {
        $this->columnLabel = $columnLabel;

        return $this;
    }

    public function getRowId(): null|int|string
    {
        return $this->rowId;
    }

    /**
     * @param int|string|null $rowId
     */
    public function setRowId(int|string $rowId): static
    {
        $this->rowId = $rowId;

        return $this;
    }

    public function merge(DataStats $data): static
    {
        $this->setCount($this->getCount() + $data->getCount());
        $this->setSum($this->getSum() + $data->getSum());

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getSum(): ?float
    {
        return $this->sum;
    }

    public function setSum(float $sum): static
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnId(): ?string
    {
        return $this->columnId;
    }

    public function setColumnId(string $columnId = null): static
    {
        $this->columnId = $columnId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRowLabel(): ?string
    {
        return $this->rowLabel;
    }

    public function setRowLabel(string $rowLabel = null): static
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }
}

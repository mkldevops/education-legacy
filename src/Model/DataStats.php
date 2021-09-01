<?php

declare(strict_types=1);

namespace App\Model;

class DataStats
{
    public function __construct(
        private ?string $columnId = null,
        private ?string $columnLabel = null,
        private null|int|string $rowId = null,
        private ?string $rowLabel = null,
        private int $count = 0,
        private float $sum = 0
    ) {
    }

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

    public function setRowId(int|string $rowId): static
    {
        $this->rowId = $rowId;

        return $this;
    }

    public function merge(DataStats $data): static
    {
        $this->count += $data->getCount();
        $this->sum += $data->getSum();

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

    public function getSum(): float
    {
        return $this->sum;
    }

    public function setSum(float $sum): static
    {
        $this->sum = $sum;

        return $this;
    }

    public function getColumnId(): ?string
    {
        return $this->columnId;
    }

    public function setColumnId(string $columnId = null): static
    {
        $this->columnId = $columnId;

        return $this;
    }

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

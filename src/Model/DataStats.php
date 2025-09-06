<?php

declare(strict_types=1);

namespace App\Model;

class DataStats
{
    /**
     * Create a DataStats instance with optional identifiers, labels and initial aggregates.
     *
     * @param string|null $columnId Optional column identifier.
     * @param string|null $columnLabel Optional human-readable column label.
     * @param int|string|null $rowId Optional row identifier (int or string).
     * @param string|null $rowLabel Optional human-readable row label.
     * @param int $count Initial count (defaults to 0).
     * @param float $sum Initial sum (defaults to 0.0).
     */
    public function __construct(
        private ?string $columnId = null,
        private ?string $columnLabel = null,
        private int|string|null $rowId = null,
        private ?string $rowLabel = null,
        private int $count = 0,
        private float $sum = 0
    ) {}

    public function getColumnLabel(): ?string
    {
        return $this->columnLabel;
    }

    /**
     * Set the label for the column.
     *
     * Passing null clears the column label.
     *
     * @param string|null $columnLabel The column label or null to unset it.
     * @return static The current instance for method chaining.
     */
    public function setColumnLabel(?string $columnLabel = null): static
    {
        $this->columnLabel = $columnLabel;

        return $this;
    }

    /**
     * Get the row identifier.
     *
     * The identifier may be an integer, a string, or null if not set.
     *
     * @return int|string|null The row ID or null when unspecified.
     */
    public function getRowId(): int|string|null
    {
        return $this->rowId;
    }

    public function setRowId(int|string $rowId): static
    {
        $this->rowId = $rowId;

        return $this;
    }

    public function merge(self $data): static
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

    public function setColumnId(?string $columnId = null): static
    {
        $this->columnId = $columnId;

        return $this;
    }

    public function getRowLabel(): ?string
    {
        return $this->rowLabel;
    }

    public function setRowLabel(?string $rowLabel = null): static
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }
}

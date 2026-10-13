<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Entities;

final class TrackingRecord
{
    /**
     * @param  array<int, FertilizationProduct>  $impulsoraProducts
     * @param  array<int, FertilizationProduct>  $otherProducts
     */
    public function __construct(
        public readonly int $stageId,
        public readonly string $stageName,
        public readonly ?string $applicationDate = null,
        public readonly bool $isTestigo = false,
        public readonly array $impulsoraProducts = [],
        public readonly array $otherProducts = [],
        public readonly ?string $advisorNotes = null,
    ) {}

    public function calculateTotalCostPerHectare(): float
    {
        $sum = 0.0;
        foreach ($this->impulsoraProducts as $p) {
            $sum += $p->calculateCostPerHectare();
        }
        foreach ($this->otherProducts as $p) {
            $sum += $p->calculateCostPerHectare();
        }

        return round($sum, 2);
    }

    public function toArray(): array
    {
        return [
            'stageId' => $this->stageId,
            'stageName' => $this->stageName,
            'applicationDate' => $this->applicationDate,
            'isTestigo' => $this->isTestigo,
            'costPerHectare' => $this->calculateTotalCostPerHectare(),
            'advisorNotes' => $this->advisorNotes,
            'impulsoraProducts' => array_map(fn (FertilizationProduct $p) => $p->toArray(), $this->impulsoraProducts),
            'otherProducts' => array_map(fn (FertilizationProduct $p) => $p->toArray(), $this->otherProducts),
        ];
    }
}

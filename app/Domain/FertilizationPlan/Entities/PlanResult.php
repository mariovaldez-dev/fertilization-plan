<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Entities;

final class PlanResult
{
    public function __construct(
        public readonly string $planCode,
        public readonly float $finalYieldTonPerHa,
        public readonly float $costPerHectare,
        public readonly float $traditionalCost,
        public readonly ?float $testigoYieldTonPerHa = null,
        public readonly ?string $harvestDate = null,
        public readonly ?string $agronomicConclusions = null,
    ) {}

    public function calculateYieldDifference(): ?float
    {
        if ($this->testigoYieldTonPerHa === null) {
            return null;
        }

        return round($this->finalYieldTonPerHa - $this->testigoYieldTonPerHa, 2);
    }

    public function calculateCostSavings(): float
    {
        return round($this->traditionalCost - $this->costPerHectare, 2);
    }

    public function toArray(): array
    {
        return [
            'planCode' => $this->planCode,
            'finalYieldTonPerHa' => $this->finalYieldTonPerHa,
            'costPerHectare' => $this->costPerHectare,
            'traditionalCost' => $this->traditionalCost,
            'testigoYieldTonPerHa' => $this->testigoYieldTonPerHa,
            'harvestDate' => $this->harvestDate,
            'agronomicConclusions' => $this->agronomicConclusions,
            'yieldDifference' => $this->calculateYieldDifference(),
            'costSavings' => $this->calculateCostSavings(),
        ];
    }
}

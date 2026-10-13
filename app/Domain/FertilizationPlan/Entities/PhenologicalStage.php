<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Entities;

final class PhenologicalStage
{
    /**
     * @param  array<int, FertilizationProduct>  $products
     */
    public function __construct(
        public readonly int $stageId,
        public readonly string $stageName,
        public readonly int $orderIndex,
        public readonly ?string $applicationMethod = null,
        public readonly ?string $observations = null,
        public readonly array $products = [],
    ) {}

    public function calculateStageCostPerHectare(): float
    {
        $total = 0.0;
        foreach ($this->products as $product) {
            $total += $product->calculateCostPerHectare();
        }

        return round($total, 2);
    }

    public function toArray(): array
    {
        return [
            'stageId' => $this->stageId,
            'stageName' => $this->stageName,
            'orderIndex' => $this->orderIndex,
            'applicationMethod' => $this->applicationMethod,
            'observations' => $this->observations,
            'costPerHectare' => $this->calculateStageCostPerHectare(),
            'products' => array_map(fn (FertilizationProduct $p) => $p->toArray(), $this->products),
        ];
    }
}

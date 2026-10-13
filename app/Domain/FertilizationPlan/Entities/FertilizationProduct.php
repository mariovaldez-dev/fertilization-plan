<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Entities;

final class FertilizationProduct
{
    public function __construct(
        public readonly string $itemCode,
        public readonly string $itemName,
        public readonly float $dosePerHectare,
        public readonly string $unitOfMeasure,
        public readonly float $unitPrice = 0.0,
        public readonly ?string $activeIngredient = null,
        public readonly ?string $applicationMethod = null,
        public readonly bool $isImpulsoraProduct = true,
    ) {}

    public function calculateCostPerHectare(): float
    {
        return round($this->dosePerHectare * $this->unitPrice, 2);
    }

    public function calculateTotalCost(float $hectares): float
    {
        return round($this->calculateCostPerHectare() * $hectares, 2);
    }

    public function toArray(): array
    {
        return [
            'itemCode' => $this->itemCode,
            'itemName' => $this->itemName,
            'dosePerHectare' => $this->dosePerHectare,
            'unitOfMeasure' => $this->unitOfMeasure,
            'unitPrice' => $this->unitPrice,
            'activeIngredient' => $this->activeIngredient,
            'applicationMethod' => $this->applicationMethod,
            'isImpulsoraProduct' => $this->isImpulsoraProduct,
            'costPerHectare' => $this->calculateCostPerHectare(),
        ];
    }
}

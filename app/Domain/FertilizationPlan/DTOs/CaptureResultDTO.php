<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class CaptureResultDTO
{
    public function __construct(
        public readonly string $planCode,
        public readonly float $finalYieldTonPerHa = 0.0,
        public readonly float $costPerHectare = 0.0,
        public readonly float $traditionalCost = 0.0,
        public readonly ?float $testigoYieldTonPerHa = null,
        public readonly ?string $harvestDate = null,
        public readonly ?string $agronomicConclusions = null,
        public readonly float $dose = 0.0,
        public readonly float $traditionalYield = 0.0,
        public readonly float $harvestPrice = 0.0,
        public readonly float $extraProfit = 0.0,
        public readonly float $extraInvestment = 0.0,
        public readonly float $returnOnInvestment = 0.0,
        public readonly string $recommendations = '',
        public readonly string $proposal = '',
        public readonly string $goodPractices = '',
        public readonly ?string $coordinates = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            planCode: (string) ($data['planCode'] ?? ''),
            finalYieldTonPerHa: (float) ($data['finalYieldTonPerHa'] ?? $data['actualYield'] ?? 0.0),
            costPerHectare: (float) ($data['costPerHectare'] ?? $data['costoTotal'] ?? 0.0),
            traditionalCost: (float) ($data['traditionalCost'] ?? $data['costoTradicional'] ?? 0.0),
            testigoYieldTonPerHa: isset($data['testigoYieldTonPerHa']) ? (float) $data['testigoYieldTonPerHa'] : null,
            harvestDate: $data['harvestDate'] ?? $data['fechaCosecha'] ?? null,
            agronomicConclusions: $data['agronomicConclusions'] ?? null,
            dose: (float) ($data['dose'] ?? $data['dosis'] ?? 0.0),
            traditionalYield: (float) ($data['traditionalYield'] ?? $data['rendimientoTradicional'] ?? 0.0),
            harvestPrice: (float) ($data['harvestPrice'] ?? $data['precioCosecha'] ?? 0.0),
            extraProfit: (float) ($data['extraProfit'] ?? $data['utilidadExtra'] ?? 0.0),
            extraInvestment: (float) ($data['extraInvestment'] ?? $data['inversionExtra'] ?? 0.0),
            returnOnInvestment: (float) ($data['returnOnInvestment'] ?? $data['retornoInversion'] ?? 0.0),
            recommendations: (string) ($data['recommendations'] ?? $data['recomendaciones'] ?? ''),
            proposal: (string) ($data['proposal'] ?? $data['propuesta'] ?? ''),
            goodPractices: (string) ($data['goodPractices'] ?? $data['buenasPracticas'] ?? ''),
            coordinates: $data['coordinates'] ?? $data['coordenadas'] ?? null,
        );
    }
}

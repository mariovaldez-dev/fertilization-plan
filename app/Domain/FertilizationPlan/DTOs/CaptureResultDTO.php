<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class CaptureResultDTO
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

    public static function fromArray(array $data): self
    {
        return new self(
            planCode: (string) ($data['planCode'] ?? ''),
            finalYieldTonPerHa: (float) ($data['finalYieldTonPerHa'] ?? 0.0),
            costPerHectare: (float) ($data['costPerHectare'] ?? 0.0),
            traditionalCost: (float) ($data['traditionalCost'] ?? 0.0),
            testigoYieldTonPerHa: isset($data['testigoYieldTonPerHa']) ? (float) $data['testigoYieldTonPerHa'] : null,
            harvestDate: $data['harvestDate'] ?? null,
            agronomicConclusions: $data['agronomicConclusions'] ?? null,
        );
    }
}

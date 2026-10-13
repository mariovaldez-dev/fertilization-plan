<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class CreatePlanDTO
{
    /**
     * @param array<int, array{
     *     stageId: int,
     *     stageName: string,
     *     orderIndex: int,
     *     applicationMethod: ?string,
     *     products: array<int, array{
     *         itemCode: string,
     *         itemName: string,
     *         dosePerHectare: float,
     *         unitOfMeasure: string,
     *         unitPrice: float,
     *         activeIngredient: ?string,
     *         applicationMethod: ?string
     *     }>
     * }> $stages
     */
    public function __construct(
        public readonly string $customerCode,
        public readonly string $customerName,
        public readonly string $advisorId,
        public readonly string $advisorName,
        public readonly string $parcela,
        public readonly string $zone,
        public readonly string $territory,
        public readonly string $season,
        public readonly float $hectares,
        public readonly string $soilType,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly bool $hasSoilAnalysis = false,
        public readonly ?string $soilAnalysisBase64 = null,
        public readonly ?string $soilAnalysisFileName = null,
        public readonly bool $hasTestigo = false,
        public readonly ?string $previousCrop = null,
        public readonly ?float $previousCropYield = null,
        public readonly ?float $previousCropMaxYield = null,
        public readonly ?string $currentCropCode = null,
        public readonly ?string $currentCropName = null,
        public readonly ?string $seedVariety = null,
        public readonly ?string $estimatedSowingDate = null,
        public readonly ?float $targetYield = null,
        public readonly ?string $irrigationSystem = null,
        public readonly array $stages = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerCode: (string) ($data['customerCode'] ?? $data['CardCode'] ?? ''),
            customerName: (string) ($data['customerName'] ?? $data['CardName'] ?? ''),
            advisorId: (string) ($data['advisorId'] ?? $data['SlpCode'] ?? ''),
            advisorName: (string) ($data['advisorName'] ?? $data['SlpName'] ?? ''),
            parcela: (string) ($data['parcela'] ?? $data['Parcela'] ?? $data['U_Parcela'] ?? ''),
            zone: (string) ($data['zone'] ?? $data['Zona'] ?? ''),
            territory: (string) ($data['territory'] ?? $data['Territorio'] ?? ''),
            season: (string) ($data['season'] ?? $data['Temporada'] ?? ''),
            hectares: (float) ($data['hectares'] ?? $data['Hectareas'] ?? 1.0),
            soilType: (string) ($data['soilType'] ?? $data['TipoSuelo'] ?? ''),
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            hasSoilAnalysis: (bool) ($data['hasSoilAnalysis'] ?? false),
            soilAnalysisBase64: $data['soilAnalysisBase64'] ?? null,
            soilAnalysisFileName: $data['soilAnalysisFileName'] ?? null,
            hasTestigo: (bool) ($data['hasTestigo'] ?? false),
            previousCrop: $data['previousCrop'] ?? null,
            previousCropYield: isset($data['previousCropYield']) ? (float) $data['previousCropYield'] : null,
            previousCropMaxYield: isset($data['previousCropMaxYield']) ? (float) $data['previousCropMaxYield'] : null,
            currentCropCode: $data['currentCropCode'] ?? null,
            currentCropName: $data['currentCropName'] ?? null,
            seedVariety: (string) ($data['seedVariety'] ?? $data['variedadSemilla'] ?? $data['VariedadSemilla'] ?? $data['U_Variedad'] ?? $data['Variedad'] ?? ''),
            estimatedSowingDate: $data['estimatedSowingDate'] ?? null,
            targetYield: isset($data['targetYield']) ? (float) $data['targetYield'] : null,
            irrigationSystem: $data['irrigationSystem'] ?? null,
            stages: $data['stages'] ?? [],
        );
    }
}

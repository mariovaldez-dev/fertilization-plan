<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Entities;

use App\Domain\FertilizationPlan\Enums\PlanStatusEnum;
use Carbon\Carbon;

final class FertilizationPlan
{
    /**
     * @param  array<int, PhenologicalStage>  $stages
     * @param  array<int, TrackingRecord>  $trackingRecords
     */
    public function __construct(
        public readonly string $planCode,
        public readonly int $version,
        public readonly string $customerCode,
        public readonly string $customerName,
        public readonly string $advisorId,
        public readonly string $advisorName,
        public readonly string $zone,
        public readonly string $territory,
        public readonly string $season,
        public readonly float $hectares,
        public readonly string $soilType,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly bool $hasSoilAnalysis = false,
        public readonly ?string $soilAnalysisFilePath = null,
        public readonly bool $hasTestigo = false,
        public readonly ?string $previousCrop = null,
        public readonly ?float $previousCropYield = null,
        public readonly ?string $currentCropCode = null,
        public readonly ?string $currentCropName = null,
        public readonly ?string $estimatedSowingDate = null,
        public readonly ?float $targetYield = null,
        public readonly PlanStatusEnum $status = PlanStatusEnum::ACTIVE,
        public readonly array $stages = [],
        public readonly array $trackingRecords = [],
        public readonly ?PlanResult $result = null,
        public readonly ?Carbon $createdAt = null,
    ) {}

    /**
     * Checks if this plan should be automatically closed based on the July 31st business rule.
     */
    public function isPastJuly31stCutoff(?Carbon $now = null): bool
    {
        $currentDate = $now ?? Carbon::now();
        // Determine the closing deadline for the current season/year
        $year = (int) $currentDate->year;
        $july31 = Carbon::create($year, 7, 31, 23, 59, 59);

        // If today is past July 31st and the plan is from this cycle or older
        if ($currentDate->greaterThan($july31)) {
            return true;
        }

        return false;
    }

    public function isReadOnly(): bool
    {
        return $this->status === PlanStatusEnum::CLOSED || $this->isPastJuly31stCutoff();
    }

    public function calculateTotalPlanCostPerHectare(): float
    {
        $total = 0.0;
        foreach ($this->stages as $stage) {
            $total += $stage->calculateStageCostPerHectare();
        }

        return round($total, 2);
    }

    public function calculateTotalCommercialCost(): float
    {
        return round($this->calculateTotalPlanCostPerHectare() * $this->hectares, 2);
    }
}

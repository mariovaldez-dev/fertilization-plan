<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Repositories;

use App\Domain\FertilizationPlan\DTOs\CaptureResultDTO;
use App\Domain\FertilizationPlan\DTOs\CreatePlanDTO;
use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Entities\FertilizationPlan;

interface FertilizationPlanRepositoryInterface
{
    /**
     * @return array{data: array<int, FertilizationPlan>, total: int, page: int, limit: int}
     */
    public function getPlansByFilter(PlanFilterDTO $filter): array;

    public function getPlanByCode(string $planCode, ?int $version = null): ?FertilizationPlan;

    public function createPlan(CreatePlanDTO $dto): array;

    public function versionPlan(string $planCode): array;

    public function cancelPlan(string $planCode, string $comment): array;

    public function saveTracking(UpdateTrackingDTO $dto): array;

    public function captureResults(CaptureResultDTO $dto): array;

    public function getProposalPdfData(string $planCode, int $version): array;

    public function getResultsPdfData(string $planCode): array;

    public function savePlanPdf(string $planCode, int $version, string $status, string $base64File): array;

    public function saveResultsPdf(string $planCode, int $version, string $status, string $base64File): array;

    public function getTrackingStages(string $planCode, int $cropId): array;

    public function getPlanForVersioning(string $planCode): array;

    public function versionPlanFull(string $planCode, array $master, array $stages): array;
}

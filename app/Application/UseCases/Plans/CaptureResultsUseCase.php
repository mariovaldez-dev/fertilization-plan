<?php

declare(strict_types=1);

namespace App\Application\UseCases\Plans;

use App\Domain\FertilizationPlan\DTOs\CaptureResultDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use InvalidArgumentException;

final class CaptureResultsUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    public function execute(CaptureResultDTO $dto): array
    {
        if (empty($dto->planCode)) {
            throw new InvalidArgumentException('El código del plan es obligatorio.');
        }

        if ($dto->finalYieldTonPerHa < 0) {
            throw new InvalidArgumentException('El rendimiento final no puede ser negativo.');
        }

        return $this->planRepository->captureResults($dto);
    }
}

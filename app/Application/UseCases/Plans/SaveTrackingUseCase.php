<?php

declare(strict_types=1);

namespace App\Application\UseCases\Plans;

use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use InvalidArgumentException;

final class SaveTrackingUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    public function execute(UpdateTrackingDTO $dto): array
    {
        if (empty($dto->planCode)) {
            throw new InvalidArgumentException('El código de plan es requerido para actualizar el seguimiento.');
        }

        return $this->planRepository->saveTracking($dto);
    }
}

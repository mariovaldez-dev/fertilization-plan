<?php

declare(strict_types=1);

namespace App\Application\UseCases\Plans;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use InvalidArgumentException;

final class VersionPlanUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    public function execute(string $planCode): array
    {
        if (empty(trim($planCode))) {
            throw new InvalidArgumentException('El código del plan es obligatorio para generar una versión.');
        }

        return $this->planRepository->versionPlan($planCode);
    }
}

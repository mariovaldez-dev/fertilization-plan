<?php

declare(strict_types=1);

namespace App\Application\UseCases\Plans;

use App\Domain\FertilizationPlan\DTOs\CreatePlanDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use InvalidArgumentException;

final class CreatePlanUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    public function execute(CreatePlanDTO $dto): array
    {
        if (empty($dto->customerCode)) {
            throw new InvalidArgumentException('El cliente es obligatorio.');
        }

        if ($dto->hectares <= 0) {
            throw new InvalidArgumentException('La superficie en hectáreas debe ser mayor a 0.');
        }

        if (empty($dto->season)) {
            throw new InvalidArgumentException('La temporada del plan es obligatoria.');
        }

        return $this->planRepository->createPlan($dto);
    }
}

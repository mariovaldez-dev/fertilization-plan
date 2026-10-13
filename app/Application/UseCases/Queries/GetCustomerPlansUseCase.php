<?php

declare(strict_types=1);

namespace App\Application\UseCases\Queries;

use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;

final class GetCustomerPlansUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @return array{data: array, total: int, page: int, limit: int}
     */
    public function execute(PlanFilterDTO $filter): array
    {
        return $this->planRepository->getPlansByFilter($filter);
    }
}

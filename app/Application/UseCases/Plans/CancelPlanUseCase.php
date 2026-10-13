<?php

declare(strict_types=1);

namespace App\Application\UseCases\Plans;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use InvalidArgumentException;

final class CancelPlanUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository
    ) {}

    public function execute(string $planCode, string $comment): array
    {
        if (empty(trim($planCode))) {
            throw new InvalidArgumentException('El código del plan es obligatorio para cancelar.');
        }

        if (empty(trim($comment))) {
            throw new InvalidArgumentException('El comentario o motivo de cancelación es obligatorio.');
        }

        return $this->planRepository->cancelPlan($planCode, $comment);
    }
}

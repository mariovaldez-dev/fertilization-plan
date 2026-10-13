<?php

declare(strict_types=1);

namespace App\Application\UseCases\Queries;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;

final class GetCropRecommendationUseCase
{
    public function __construct(
        private readonly CatalogsRepositoryInterface $catalogsRepository
    ) {}

    public function execute(string $cropId, ?string $territory = null): array
    {
        return $this->catalogsRepository->getCropRecommendation($cropId, $territory);
    }
}

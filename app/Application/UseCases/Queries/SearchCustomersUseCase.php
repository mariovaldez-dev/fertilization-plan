<?php

declare(strict_types=1);

namespace App\Application\UseCases\Queries;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;

final class SearchCustomersUseCase
{
    public function __construct(
        private readonly CatalogsRepositoryInterface $catalogsRepository
    ) {}

    public function execute(string $query, ?string $zone = null, int $page = 1, int $limit = 10): array
    {
        return $this->catalogsRepository->searchCustomers($query, $zone, $page, $limit);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class PlanFilterDTO
{
    public function __construct(
        public readonly ?string $customerCode = null,
        public readonly ?string $zone = null,
        public readonly ?string $territory = null,
        public readonly ?string $season = null,
        public readonly ?bool $onlyClosed = false,
        public readonly ?string $searchQuery = null,
        public readonly int $page = 1,
        public readonly int $limit = 10,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerCode: $data['customerCode'] ?? $data['CardCode'] ?? null,
            zone: $data['zone'] ?? null,
            territory: $data['territory'] ?? null,
            season: $data['season'] ?? null,
            onlyClosed: (bool) ($data['onlyClosed'] ?? false),
            searchQuery: $data['searchQuery'] ?? $data['texto'] ?? null,
            page: (int) ($data['page'] ?? 1),
            limit: (int) ($data['limit'] ?? 10),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class UpdateTrackingDTO
{
    /**
     * @param  array<int, mixed>  $stages
     * @param  array<int, mixed>  $records
     */
    public function __construct(
        public readonly string $planCode,
        public readonly bool $isTestigo = false,
        public readonly array $stages = [],
        public readonly array $records = [],
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function getRecords(): array
    {
        return ! empty($this->records) ? $this->records : $this->stages;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            planCode: (string) ($data['planCode'] ?? ''),
            isTestigo: (bool) ($data['isTestigo'] ?? false),
            stages: (array) ($data['stages'] ?? []),
            records: (array) ($data['records'] ?? []),
        );
    }
}

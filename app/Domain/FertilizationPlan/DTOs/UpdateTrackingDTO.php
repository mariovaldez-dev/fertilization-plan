<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\DTOs;

final class UpdateTrackingDTO
{
    /**
     * @param array<int, array{
     *     stageId: int,
     *     stageName: string,
     *     applicationDate: ?string,
     *     isTestigo: bool,
     *     impulsoraProducts: array,
     *     otherProducts: array,
     *     advisorNotes: ?string
     * }> $stages
     */
    public function __construct(
        public readonly string $planCode,
        public readonly bool $isTestigo = false,
        public readonly array $stages = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            planCode: (string) ($data['planCode'] ?? ''),
            isTestigo: (bool) ($data['isTestigo'] ?? false),
            stages: (array) ($data['stages'] ?? []),
        );
    }
}

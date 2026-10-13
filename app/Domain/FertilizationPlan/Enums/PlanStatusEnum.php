<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Enums;

enum PlanStatusEnum: string
{
    case DRAFT = 'Borrador';
    case ACTIVE = 'Activo';
    case CLOSED = 'Cerrado';
    case CANCELLED = 'Cancelado';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::ACTIVE => 'Activo',
            self::CLOSED => 'Cerrado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'amber',
            self::ACTIVE => 'green',
            self::CLOSED => 'slate',
            self::CANCELLED => 'red',
        };
    }
}

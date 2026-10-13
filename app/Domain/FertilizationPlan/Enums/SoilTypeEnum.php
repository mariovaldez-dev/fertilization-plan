<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Enums;

enum SoilTypeEnum: string
{
    case SANDY = 'Arenoso';
    case CLAY = 'Arcilloso';
    case LOAM = 'Franco';
    case CLAY_LOAM = 'Franco Arcilloso';
    case SANDY_LOAM = 'Franco Arenoso';
    case SILT_LOAM = 'Franco Limoso';
}

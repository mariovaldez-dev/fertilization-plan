<?php

declare(strict_types=1);

namespace App\Domain\FertilizationPlan\Enums;

enum ApplicationMethodEnum: string
{
    case FERTIRRIGATION = 'Fertirriego';
    case FOLIAR = 'Foliar';
    case SOIL = 'Edáfico / Al Suelo';
    case DRIP = 'Goteo';
    case SPRINKLER = 'Aspersión';
}

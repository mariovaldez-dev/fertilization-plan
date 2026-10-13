<?php

declare(strict_types=1);

namespace App\Application\UseCases\Reports;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Pdf\DomPdfService;
use InvalidArgumentException;

final class GenerateResultsPdfUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository,
        private readonly DomPdfService $pdfService
    ) {}

    public function execute(string $planCode): array
    {
        if (empty($planCode)) {
            throw new InvalidArgumentException('El código del plan es obligatorio para el PDF de resultados.');
        }

        $resultsData = $this->planRepository->getResultsPdfData($planCode);
        $pdfOutput = $this->pdfService->generateResultsPdf($resultsData);

        return [
            'planCode' => $planCode,
            'pdfBase64' => base64_encode($pdfOutput),
            'fileName' => "Dictamen_Resultados_{$planCode}.pdf",
        ];
    }
}

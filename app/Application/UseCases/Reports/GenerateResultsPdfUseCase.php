<?php

declare(strict_types=1);

namespace App\Application\UseCases\Reports;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Pdf\DomPdfService;
use Illuminate\Support\Facades\Log;
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

        $startTime = microtime(true);
        Log::info('[GenerateResultsPdfUseCase] Results PDF generation started', [
            'planCode' => $planCode,
        ]);

        try {
            $resultsData = $this->planRepository->getResultsPdfData($planCode);
            $pdfOutput = $this->pdfService->generateResultsPdf($resultsData);
            $base64 = base64_encode($pdfOutput);

            Log::info('[GenerateResultsPdfUseCase] Results PDF generated successfully', [
                'planCode' => $planCode,
                'bytes' => strlen($pdfOutput),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [
                'planCode' => $planCode,
                'pdfBase64' => $base64,
                'fileName' => "Dictamen_Resultados_{$planCode}.pdf",
            ];
        } catch (\Throwable $e) {
            Log::error('[GenerateResultsPdfUseCase] Results PDF generation failed', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            throw $e;
        }
    }
}

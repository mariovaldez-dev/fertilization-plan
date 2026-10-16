<?php

declare(strict_types=1);

namespace App\Application\UseCases\Reports;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Pdf\DomPdfService;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class GenerateProposalPdfUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository,
        private readonly DomPdfService $pdfService
    ) {}

    public function execute(string $planCode, int $version = 1, ?string $customerName = null): array
    {
        if (empty($planCode)) {
            throw new InvalidArgumentException('El código del plan es obligatorio.');
        }

        $startTime = microtime(true);
        Log::info('[GenerateProposalPdfUseCase] Proposal PDF execution started', [
            'planCode' => $planCode,
            'version' => $version,
            'customerName' => $customerName,
        ]);

        // 1. Intentar obtener el PDF almacenado directamente en SQL Server vía API
        $storedPdf = $this->planRepository->getStoredPlanPdf($planCode, $version);
        if (! empty($storedPdf)) {
            Log::info('[GenerateProposalPdfUseCase] Using stored PDF from database', [
                'planCode' => $planCode,
                'version' => $version,
                'bytes' => strlen($storedPdf),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [
                'planCode' => $planCode,
                'version' => $version,
                'pdfBase64' => $storedPdf,
                'fileName' => "Plan_Fertilizacion_{$planCode}_v{$version}.pdf",
            ];
        }

        Log::info('[GenerateProposalPdfUseCase] Stored PDF not found in DB. Generating dynamically via DomPdf', [
            'planCode' => $planCode,
            'version' => $version,
        ]);

        // 2. Si no existe almacenado, construir datos del plan y generar vía DomPdf
        $planData = $this->planRepository->getProposalPdfData($planCode, $version);
        if (! empty($customerName)) {
            $planData['customerName'] = $customerName;
            $planData['productor'] = $customerName;
            $planData['CardName'] = $customerName;
        }

        $pdfOutput = $this->pdfService->generateProposalPdf($planData);

        Log::info('[GenerateProposalPdfUseCase] Dynamic proposal PDF generated', [
            'planCode' => $planCode,
            'version' => $version,
            'bytes' => strlen($pdfOutput),
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);

        return [
            'planCode' => $planCode,
            'version' => $version,
            'pdfBase64' => base64_encode($pdfOutput),
            'fileName' => "Plan_Fertilizacion_{$planCode}_v{$version}.pdf",
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Application\UseCases\Reports;

use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Pdf\DomPdfService;
use InvalidArgumentException;

final class GenerateProposalPdfUseCase
{
    public function __construct(
        private readonly FertilizationPlanRepositoryInterface $planRepository,
        private readonly DomPdfService $pdfService
    ) {}

    public function execute(string $planCode, int $version = 1): array
    {
        if (empty($planCode)) {
            throw new InvalidArgumentException('El código del plan es obligatorio.');
        }

        $planData = $this->planRepository->getProposalPdfData($planCode, $version);
        $pdfOutput = $this->pdfService->generateProposalPdf($planData);

        return [
            'planCode' => $planCode,
            'version' => $version,
            'pdfBase64' => base64_encode($pdfOutput),
            'fileName' => "Plan_Fertilizacion_{$planCode}_v{$version}.pdf",
        ];
    }
}

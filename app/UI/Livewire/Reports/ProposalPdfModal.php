<?php

declare(strict_types=1);

namespace App\UI\Livewire\Reports;

use App\Application\UseCases\Reports\GenerateProposalPdfUseCase;
use App\Application\UseCases\WhatsApp\SendPlanWhatsAppUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

final class ProposalPdfModal extends Component
{
    public bool $isOpen = false;

    public string $planCode = '';

    public int $version = 1;

    public string $customerName = '';

    public string $recipientPhone = '';

    public bool $isPhoneMissing = false;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public bool $isGenerating = false;

    public bool $isSharing = false;

    public ?string $pdfBase64 = null;

    #[On('open-proposal-pdf')]
    public function open(
        string $planCode,
        int $version,
        string $customerName,
        ?string $phone = null,
        ?string $customerCode = null
    ): void {
        $this->planCode = $planCode;
        $this->version = $version;
        $this->customerName = $customerName;
        $this->recipientPhone = trim((string) ($phone ?? ''));
        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->isGenerating = false;
        $this->isSharing = false;
        $this->pdfBase64 = null;
        $this->isOpen = true;

        $digits = preg_replace('/[^0-9]/', '', (string) $this->recipientPhone);

        if (strlen($digits) < 10 && ! empty($customerCode)) {
            try {
                $catalogs = app(CatalogsRepositoryInterface::class);
                $found = $catalogs->searchCustomers($customerCode);
                foreach ($found as $c) {
                    $foundDigits = preg_replace('/[^0-9]/', '', (string) ($c['Cellular'] ?? ''));
                    if (($c['CardCode'] ?? '') === $customerCode && strlen($foundDigits) >= 10) {
                        $this->recipientPhone = trim((string) $c['Cellular']);
                        $digits = $foundDigits;
                        break;
                    }
                }
            } catch (\Throwable) {
                // Ignore fallback errors
            }
        }

        $this->isPhoneMissing = strlen($digits) < 10;

        Log::info('[ProposalPdfModal] Modal opened', [
            'planCode' => $planCode,
            'version' => $version,
            'customerName' => $customerName,
            'recipientPhone' => $this->recipientPhone,
            'customerCode' => $customerCode,
            'isPhoneMissing' => $this->isPhoneMissing,
        ]);
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->pdfBase64 = null;
        $this->isGenerating = false;
        $this->isSharing = false;
    }

    public function getIsPhoneMissingProperty(): bool
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $this->recipientPhone);

        return strlen($digits) < 10;
    }

    public function download(GenerateProposalPdfUseCase $useCase)
    {
        if ($this->isGenerating || $this->isSharing) {
            return;
        }

        $this->errorMessage = null;
        $this->statusMessage = null;
        $this->isGenerating = true;
        $startTime = microtime(true);

        Log::info('[ProposalPdfModal] Download initiated', [
            'planCode' => $this->planCode,
            'version' => $this->version,
            'cached' => ! empty($this->pdfBase64),
        ]);

        try {
            if (empty($this->pdfBase64)) {
                $report = $useCase->execute($this->planCode, $this->version, $this->customerName);
                $this->pdfBase64 = $report['pdfBase64'] ?? null;
            }

            if (empty($this->pdfBase64)) {
                throw new \RuntimeException('No se pudo obtener el contenido del PDF.');
            }

            $binary = base64_decode($this->pdfBase64);
            $fileName = "PlanFertilizacion_{$this->planCode}_V{$this->version}.pdf";

            $this->statusMessage = '¡Descarga iniciada exitosamente!';

            Log::info('[ProposalPdfModal] Download prepared successfully', [
                'planCode' => $this->planCode,
                'version' => $this->version,
                'bytes' => strlen($binary),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return response()->streamDownload(
                fn () => print ($binary),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            $this->statusMessage = null;
            $this->errorMessage = $e->getMessage() ?: 'Error al descargar el PDF.';
            Log::error('[ProposalPdfModal] Download failed', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function share(
        GenerateProposalPdfUseCase $pdfUseCase,
        SendPlanWhatsAppUseCase $whatsAppUseCase
    ): void {
        if ($this->isSharing || $this->isGenerating) {
            return;
        }

        $this->errorMessage = null;
        $this->statusMessage = null;

        if ($this->isPhoneMissing) {
            $this->errorMessage = 'No se puede compartir: Falta el número de teléfono celular del cliente.';
            Log::warning('[ProposalPdfModal] WhatsApp share blocked: phone is missing', [
                'planCode' => $this->planCode,
            ]);

            return;
        }

        $this->isSharing = true;
        $startTime = microtime(true);

        Log::info('[ProposalPdfModal] WhatsApp share initiated', [
            'planCode' => $this->planCode,
            'version' => $this->version,
            'recipientPhone' => $this->recipientPhone,
            'cached' => ! empty($this->pdfBase64),
        ]);

        $clientName = $this->customerName ?: 'Cliente';
        $mensajeCliente = "🌱 ¡Tu Plan de Fertilización está listo! 🌱\n\n"
            ."Hola 👋\n"
            ."Te compartimos tu Plan de Fertilización para {$clientName}.\n\n"
            ."📄 Adjunto encontrarás el documento con las recomendaciones agronómicas.\n\n"
            .'¡Estamos a tus órdenes! 🌾';

        try {
            if (empty($this->pdfBase64)) {
                $report = $pdfUseCase->execute($this->planCode, $this->version, $this->customerName);
                $this->pdfBase64 = $report['pdfBase64'] ?? null;
            }

            if (empty($this->pdfBase64)) {
                throw new \RuntimeException('La consulta del PDF falló.');
            }

            $message = new WhatsAppMessageDTO(
                recipientPhone: $this->recipientPhone,
                customerName: $clientName,
                pdfBase64: $this->pdfBase64,
                fileName: "PlanFertilizacion_{$this->planCode}_V{$this->version}.pdf",
                customMessage: $mensajeCliente,
            );

            $result = $whatsAppUseCase->execute($message);

            if ($result['success']) {
                $this->errorMessage = null;
                $this->statusMessage = $result['message'] ?? 'Plan enviado al cliente exitosamente.';
                Log::info('[ProposalPdfModal] WhatsApp share succeeded', [
                    'planCode' => $this->planCode,
                    'recipientPhone' => $this->recipientPhone,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);
            } else {
                $this->statusMessage = null;
                $this->errorMessage = $result['message'] ?? 'Error al enviar por WhatsApp.';
                Log::warning('[ProposalPdfModal] WhatsApp share error response', [
                    'planCode' => $this->planCode,
                    'recipientPhone' => $this->recipientPhone,
                    'result' => $result,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);
            }
        } catch (\Throwable $e) {
            $this->statusMessage = null;
            $this->errorMessage = $e->getMessage() ?: 'Ocurrió un error al intentar compartir.';
            Log::error('[ProposalPdfModal] WhatsApp share exception', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } finally {
            $this->isSharing = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.proposal-pdf-modal');
    }
}

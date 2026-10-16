<?php

declare(strict_types=1);

namespace App\UI\Livewire\Reports;

use App\Application\UseCases\Reports\GenerateResultsPdfUseCase;
use App\Application\UseCases\WhatsApp\SendPlanWhatsAppUseCase;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

final class ResultsPdfModal extends Component
{
    public bool $isOpen = false;

    public string $planCode = '';

    public string $customerName = '';

    public string $recipientPhone = '';

    public ?string $customMessage = null;

    public ?string $pdfBase64 = null;

    public bool $isSendingWhatsApp = false;

    #[On('open-results-pdf')]
    public function open(
        string $planCode,
        string $customerName,
        GenerateResultsPdfUseCase $generatePdfUseCase
    ): void {
        $startTime = microtime(true);
        $this->planCode = $planCode;
        $this->customerName = $customerName;

        Log::info('[ResultsPdfModal] Modal opened', [
            'planCode' => $planCode,
            'customerName' => $customerName,
        ]);

        try {
            $report = $generatePdfUseCase->execute($planCode);
            $this->pdfBase64 = $report['pdfBase64'] ?? null;
            $this->isOpen = true;

            Log::info('[ResultsPdfModal] Modal initialized with results report', [
                'planCode' => $planCode,
                'bytes' => strlen($this->pdfBase64 ?? ''),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            Log::error('[ResultsPdfModal] Error generating report on modal open', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
            $this->isOpen = true;
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->pdfBase64 = null;
    }

    public function sendViaWhatsApp(SendPlanWhatsAppUseCase $whatsAppUseCase): void
    {
        $this->validate([
            'recipientPhone' => 'required|min:10',
        ]);

        if (empty($this->pdfBase64)) {
            Log::warning('[ResultsPdfModal] WhatsApp send blocked: PDF is empty', [
                'planCode' => $this->planCode,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se ha podido generar el dictamen para envío.',
            ]);

            return;
        }

        $this->isSendingWhatsApp = true;
        $startTime = microtime(true);
        $phone = preg_replace('/\D/', '', (string) $this->recipientPhone);

        Log::info('[ResultsPdfModal] Sending results document via WhatsApp', [
            'planCode' => $this->planCode,
            'recipientPhone' => $phone,
        ]);

        try {
            $message = new WhatsAppMessageDTO(
                recipientPhone: $phone,
                customerName: $this->customerName,
                pdfBase64: $this->pdfBase64,
                fileName: "Dictamen_Resultados_{$this->planCode}.pdf",
                customMessage: $this->customMessage,
            );

            $result = $whatsAppUseCase->execute($message);

            if ($result['success']) {
                Log::info('[ResultsPdfModal] Results document sent via WhatsApp successfully', [
                    'planCode' => $this->planCode,
                    'recipientPhone' => $phone,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => '¡Dictamen de resultados enviado por WhatsApp!',
                ]);
                $this->close();
            } else {
                Log::warning('[ResultsPdfModal] WhatsApp dispatch returned error', [
                    'planCode' => $this->planCode,
                    'recipientPhone' => $phone,
                    'result' => $result,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => $result['message'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[ResultsPdfModal] WhatsApp dispatch exception', [
                'planCode' => $this->planCode,
                'recipientPhone' => $phone,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al enviar por WhatsApp: '.$e->getMessage(),
            ]);
        } finally {
            $this->isSendingWhatsApp = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.results-pdf-modal');
    }
}

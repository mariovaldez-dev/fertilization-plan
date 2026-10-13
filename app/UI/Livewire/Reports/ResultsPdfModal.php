<?php

declare(strict_types=1);

namespace App\UI\Livewire\Reports;

use App\Application\UseCases\Reports\GenerateResultsPdfUseCase;
use App\Application\UseCases\WhatsApp\SendPlanWhatsAppUseCase;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
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
        $this->planCode = $planCode;
        $this->customerName = $customerName;

        $report = $generatePdfUseCase->execute($planCode);
        $this->pdfBase64 = $report['pdfBase64'] ?? null;
        $this->isOpen = true;
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
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se ha podido generar el dictamen para envío.',
            ]);

            return;
        }

        $this->isSendingWhatsApp = true;

        $message = new WhatsAppMessageDTO(
            recipientPhone: $this->recipientPhone,
            customerName: $this->customerName,
            pdfBase64: $this->pdfBase64,
            fileName: "Dictamen_Resultados_{$this->planCode}.pdf",
            customMessage: $this->customMessage,
        );

        $result = $whatsAppUseCase->execute($message);
        $this->isSendingWhatsApp = false;

        if ($result['success']) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => '¡Dictamen de resultados enviado por WhatsApp!',
            ]);
            $this->close();
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['message'],
            ]);
        }
    }

    public function render()
    {
        return view('livewire.reports.results-pdf-modal');
    }
}

<?php

declare(strict_types=1);

namespace App\UI\Livewire\Reports;

use App\Application\UseCases\Reports\GenerateProposalPdfUseCase;
use App\Application\UseCases\WhatsApp\SendPlanWhatsAppUseCase;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use Livewire\Attributes\On;
use Livewire\Component;

final class ProposalPdfModal extends Component
{
    public bool $isOpen = false;

    public string $planCode = '';

    public int $version = 1;

    public string $customerName = '';

    public string $recipientPhone = '';

    public ?string $customMessage = null;

    public ?string $pdfBase64 = null;

    public bool $isSendingWhatsApp = false;

    #[On('open-proposal-pdf')]
    public function open(
        string $planCode,
        int $version,
        string $customerName,
        GenerateProposalPdfUseCase $generatePdfUseCase,
        ?string $phone = null
    ): void {
        $this->planCode = $planCode;
        $this->version = $version;
        $this->customerName = $customerName;
        $this->recipientPhone = (string) ($phone ?? '');

        $report = $generatePdfUseCase->execute($planCode, $version);
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
                'message' => 'No se ha podido generar el PDF para envío.',
            ]);

            return;
        }

        $this->isSendingWhatsApp = true;

        $message = new WhatsAppMessageDTO(
            recipientPhone: $this->recipientPhone,
            customerName: $this->customerName,
            pdfBase64: $this->pdfBase64,
            fileName: "Propuesta_Fertilizacion_{$this->planCode}.pdf",
            customMessage: $this->customMessage,
        );

        $result = $whatsAppUseCase->execute($message);
        $this->isSendingWhatsApp = false;

        if ($result['success']) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => '¡Propuesta enviada por WhatsApp exitosamente!',
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
        return view('livewire.reports.proposal-pdf-modal');
    }
}

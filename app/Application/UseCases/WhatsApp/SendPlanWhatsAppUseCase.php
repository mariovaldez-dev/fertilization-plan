<?php

declare(strict_types=1);

namespace App\Application\UseCases\WhatsApp;

use App\Domain\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use InvalidArgumentException;

final class SendPlanWhatsAppUseCase
{
    public function __construct(
        private readonly WhatsAppServiceInterface $whatsAppService
    ) {}

    public function execute(WhatsAppMessageDTO $message): array
    {
        if (empty($message->recipientPhone)) {
            throw new InvalidArgumentException('El número de teléfono del destinatario es obligatorio.');
        }

        if (empty($message->pdfBase64)) {
            throw new InvalidArgumentException('El archivo PDF es requerido para enviar por WhatsApp.');
        }

        return $this->whatsAppService->sendDocument($message);
    }
}

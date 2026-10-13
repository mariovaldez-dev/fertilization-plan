<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\DTOs;

final class WhatsAppMessageDTO
{
    public function __construct(
        public readonly string $recipientPhone,
        public readonly string $customerName,
        public readonly string $pdfBase64,
        public readonly string $fileName,
        public readonly ?string $customMessage = null,
    ) {}

    public function getCaption(): string
    {
        if (! empty($this->customMessage)) {
            return $this->customMessage;
        }

        return "🌱 ¡Tu Plan de Fertilización está listo! 🌱\n\n"
            ."Hola 👋\n"
            ."Te compartimos tu Plan de Fertilización para {$this->customerName}.\n\n"
            ."📄 Adjunto encontrarás el documento con las recomendaciones agronómicas.\n\n"
            .'¡Estamos a tus órdenes! 🌾';
    }
}

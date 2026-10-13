<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\Contracts;

use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;

interface WhatsAppServiceInterface
{
    /**
     * @return array{success: bool, message: string}
     */
    public function sendDocument(WhatsAppMessageDTO $message): array;
}

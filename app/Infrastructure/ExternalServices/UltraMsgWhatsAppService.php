<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalServices;

use App\Domain\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UltraMsgWhatsAppService implements WhatsAppServiceInterface
{
    private string $instanceId;

    private string $token;

    private string $apiUrl;

    public function __construct()
    {
        $this->instanceId = (string) config('services.ultramsg.instance_id', 'instance30943');
        $this->token = (string) config('services.ultramsg.token', 'jmcd6zz2c1jq1je0');
        $this->apiUrl = (string) config('services.ultramsg.url', "https://api.ultramsg.com/{$this->instanceId}/messages/document");
    }

    public function sendDocument(WhatsAppMessageDTO $message): array
    {
        try {
            $formattedBase64 = str_starts_with($message->pdfBase64, 'data:application/pdf;base64,')
                ? $message->pdfBase64
                : "data:application/pdf;base64,{$message->pdfBase64}";

            $payload = [
                'token' => $this->token,
                'to' => $message->recipientPhone,
                'filename' => $message->fileName,
                'document' => $formattedBase64,
                'caption' => $message->getCaption(),
            ];

            $response = Http::timeout(30)->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (! empty($data['sent'])) {
                    return [
                        'success' => true,
                        'message' => '¡El plan fue enviado exitosamente por WhatsApp!',
                    ];
                }
            }

            Log::error('UltraMSG WhatsApp error response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar por WhatsApp: '.($response->json('error.message') ?? 'Error en el servicio de mensajería.'),
            ];
        } catch (Throwable $e) {
            Log::error('Exception in UltraMsgWhatsAppService::sendDocument', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Error de conexión con WhatsApp: '.$e->getMessage(),
            ];
        }
    }
}

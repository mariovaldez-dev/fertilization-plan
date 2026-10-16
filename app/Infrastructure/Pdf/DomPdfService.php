<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Throwable;

final class DomPdfService
{
    public function generateProposalPdf(array $planData): string
    {
        $planCode = (string) ($planData['PlanCode'] ?? $planData['planCode'] ?? $planData['id'] ?? 'PF-000');
        $parcela = (string) ($planData['parcela'] ?? $planData['Parcela'] ?? 'General');
        $cardCode = (string) ($planData['CardCode'] ?? $planData['cardCode'] ?? '');

        // Formato requerido del mensaje / QR: {PlanCode} - Plan {Parcela} - {CardCode}
        $qrContent = ! empty($planData['mensaje'])
            ? (string) $planData['mensaje']
            : "{$planCode} - Plan {$parcela}".($cardCode !== '' ? " - {$cardCode}" : '');

        // Priorizar captura del mapa Leaflet enviada desde el frontend
        $mapBase64 = ! empty($planData['mapImage']) && str_starts_with((string) $planData['mapImage'], 'data:image/')
            ? (string) $planData['mapImage']
            : (! empty($planData['mapImageDataUrl']) && str_starts_with((string) $planData['mapImageDataUrl'], 'data:image/')
                ? (string) $planData['mapImageDataUrl']
                : $this->generateMapBase64($planData['coordenadas'] ?? $planData['Coordenadas'] ?? null));

        $qrBase64 = $this->generateQrBase64($qrContent);
        $logoBase64 = $this->getLogoBase64();

        // Aseguramos que $planData['mensaje'] contenga el formato institucional
        $planData['mensaje'] = $qrContent;

        $pdf = Pdf::loadView('pdf.proposal-report', [
            'plan' => $planData,
            'qrCode' => $qrBase64,
            'mapBase64' => $mapBase64,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->output();
    }

    public function generateResultsPdf(array $resultsData): string
    {
        $planCode = (string) ($resultsData['planInfo']['PlanCode'] ?? 'PF-DEMO');
        $parcela = (string) ($resultsData['planInfo']['Parcela'] ?? $resultsData['planInfo']['U_Parcela'] ?? 'General');
        $cardCode = (string) ($resultsData['planInfo']['CardCode'] ?? '');
        $qrContent = "{$planCode} - Plan {$parcela}".($cardCode !== '' ? " - {$cardCode}" : '');

        $coords = $resultsData['planInfo']['U_Coordenadas'] ?? $resultsData['planInfo']['Coordenadas'] ?? null;
        $mapBase64 = $this->generateMapBase64($coords);
        $logoBase64 = $this->getLogoBase64();
        $qrBase64 = $this->generateQrBase64($qrContent);

        $pdf = Pdf::loadView('pdf.results-report', [
            'data' => $resultsData,
            'qrCode' => $qrBase64,
            'mapBase64' => $mapBase64,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->output();
    }

    private function generateMapBase64(?string $coordinates): ?string
    {
        if (empty($coordinates) || ! str_contains($coordinates, ',')) {
            return null;
        }

        $parts = array_map('trim', explode(',', $coordinates));
        if (count($parts) < 2) {
            return null;
        }

        $lat = (float) $parts[0];
        $lon = (float) $parts[1];

        if ($lat === 0.0 || $lon === 0.0) {
            return null;
        }

        // 1. Obtener imagen satelital/híbrida con marcador de Yandex (estándar institucional de la versión React)
        try {
            $zoomLevel = 13;
            $mapType = 'map,skl'; // Híbrido con satélite y vialidades
            $marker = "pt={$lon},{$lat},pm2rdm"; // Marcador rojo en la coordenada
            $yandexUrl = "https://static-maps.yandex.ru/1.x/?lang=es_ES&ll={$lon},{$lat}&z={$zoomLevel}&l={$mapType}&size=450,260&{$marker}";

            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 3.5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $imageBytes = @file_get_contents($yandexUrl, false, $ctx);
            if ($imageBytes !== false && strlen($imageBytes) > 500) {
                return 'data:image/png;base64,'.base64_encode($imageBytes);
            }
        } catch (Throwable) {
            // Continúa al canvas de contingencia
        }

        // 2. Canvas local de contingencia en caso de no tener salida a internet
        try {
            if (! extension_loaded('gd')) {
                return null;
            }

            $out = imagecreatetruecolor(450, 260);

            // Fondo de terreno institucional suave
            $bg = imagecolorallocate($out, 240, 245, 238);
            imagefill($out, 0, 0, $bg);

            // Cuadrícula sutil de parcela / topográfica
            $gridColor = imagecolorallocatealpha($out, 180, 200, 175, 70);
            for ($x = 0; $x < 450; $x += 30) {
                imageline($out, $x, 0, $x, 260, $gridColor);
            }
            for ($y = 0; $y < 260; $y += 30) {
                imageline($out, 0, $y, 450, $y, $gridColor);
            }

            // Marcador institucional centrado en la parcela
            $white = imagecolorallocate($out, 255, 255, 255);
            $green = imagecolorallocate($out, 22, 163, 74);
            $shadow = imagecolorallocatealpha($out, 0, 0, 0, 75);

            imagefilledellipse($out, 225, 122, 22, 22, $shadow);
            imagefilledellipse($out, 225, 120, 18, 18, $white);
            imagefilledellipse($out, 225, 120, 14, 14, $green);

            // Banner inferior con coordenadas GPS
            $bannerBg = imagecolorallocatealpha($out, 255, 255, 255, 20);
            imagefilledrectangle($out, 0, 225, 450, 260, $bannerBg);
            $textColor = imagecolorallocate($out, 60, 80, 60);
            $coordText = "GPS: {$lat}, {$lon}";
            imagestring($out, 3, 20, 235, $coordText, $textColor);

            ob_start();
            imagejpeg($out, null, 90);
            $finalJpeg = (string) ob_get_clean();
            imagedestroy($out);

            return 'data:image/jpeg;base64,'.base64_encode($finalJpeg);
        } catch (Throwable) {
            return null;
        }
    }

    private function generateQrBase64(string $code): string
    {
        try {
            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'scale' => 8,
                'imageBase64' => true,
            ]);

            return (new QRCode($options))->render($code);
        } catch (Throwable) {
            try {
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=2&data='.urlencode($code);
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'user_agent' => 'Mozilla/5.0',
                    ],
                ]);
                $img = @file_get_contents($qrUrl, false, $ctx);
                if ($img !== false && strlen($img) > 100) {
                    return 'data:image/png;base64,'.base64_encode($img);
                }
            } catch (Throwable) {
            }
        }

        return '';
    }

    private function getLogoBase64(): ?string
    {
        $path = public_path('impulsora.png');
        if (file_exists($path)) {
            return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
        }

        return null;
    }
}

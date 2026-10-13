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
        $parcela = (string) ($resultsData['planInfo']['Parcela'] ?? 'General');
        $cardCode = (string) ($resultsData['planInfo']['CardCode'] ?? '');
        $qrContent = "{$planCode} - Plan {$parcela}".($cardCode !== '' ? " - {$cardCode}" : '');

        $pdf = Pdf::loadView('pdf.results-report', [
            'data' => $resultsData,
            'qrCode' => $this->generateQrBase64($qrContent),
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->output();
    }

    private function generateMapBase64(?string $coordinates): ?string
    {
        if (empty($coordinates) || ! str_contains($coordinates, ',')) {
            return null;
        }

        try {
            $parts = array_map('trim', explode(',', $coordinates));
            if (count($parts) < 2) {
                return null;
            }

            $lat = (float) $parts[0];
            $lon = (float) $parts[1];

            if ($lat === 0.0 || $lon === 0.0) {
                return null;
            }

            $zoom = 14;
            $n = pow(2, $zoom);
            $xExact = ($lon + 180) / 360 * $n;
            $yExact = (1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / M_PI) / 2 * $n;

            $x0 = (int) floor($xExact);
            $y0 = (int) floor($yExact);

            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ],
            ]);

            $out = imagecreatetruecolor(450, 260);

            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = -1; $dy <= 1; $dy++) {
                    $tx = $x0 + $dx;
                    $ty = $y0 + $dy;
                    $tileUrl = "https://a.tile.opentopomap.org/{$zoom}/{$tx}/{$ty}.png";
                    $tileContent = @file_get_contents($tileUrl, false, $ctx);
                    if ($tileContent) {
                        $tileImg = @imagecreatefromstring($tileContent);
                        if ($tileImg) {
                            $px = 225 + ($tx - $xExact) * 256;
                            $py = 130 + ($ty - $yExact) * 256;
                            imagecopy($out, $tileImg, (int) $px, (int) $py, 0, 0, 256, 256);
                            imagedestroy($tileImg);
                        }
                    }
                }
            }

            // Marcador institucional centrado en la parcela
            $white = imagecolorallocate($out, 255, 255, 255);
            $green = imagecolorallocate($out, 22, 163, 74);
            $shadow = imagecolorallocatealpha($out, 0, 0, 0, 60);

            imagefilledellipse($out, 225, 132, 18, 18, $shadow);
            imagefilledellipse($out, 225, 130, 16, 16, $white);
            imagefilledellipse($out, 225, 130, 12, 12, $green);

            ob_start();
            imagejpeg($out, null, 85);
            $finalJpeg = (string) ob_get_clean();
            imagedestroy($out);

            return 'data:image/jpeg;base64,'.base64_encode($finalJpeg);
        } catch (Throwable) {
            // Silently fall back to null
        }

        return null;
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

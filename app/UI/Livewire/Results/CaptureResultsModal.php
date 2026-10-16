<?php

declare(strict_types=1);

namespace App\UI\Livewire\Results;

use App\Application\UseCases\Plans\CaptureResultsUseCase;
use App\Application\UseCases\Reports\GenerateResultsPdfUseCase;
use App\Domain\FertilizationPlan\DTOs\CaptureResultDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use App\Domain\WhatsApp\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

final class CaptureResultsModal extends Component
{
    public bool $isOpen = false;

    public bool $isLoading = false;

    public string $planCode = '';

    public int $version = 1;

    public string $customerName = '';

    public ?string $customerPhone = null;

    public string $fechaCosecha = '';

    public ?float $rendimiento = null;

    public ?float $rendimientoTradicional = null;

    public ?float $dosis = null;

    public ?float $precioCosecha = null;

    public ?float $costoTotal = null;

    public ?float $costoTradicional = null;

    public float $utilidadExtra = 0.0;

    public float $inversionExtra = 0.0;

    public float $retornoInversion = 0.0;

    public string $recomendaciones = '';

    public string $propuesta = '';

    public string $buenasPracticas = '';

    public ?string $coordenadas = null;

    public bool $isSaving = false;

    public bool $isSuccess = false;

    public string $errorMessage = '';

    public ?string $pdfBase64 = null;

    public bool $isSharing = false;

    #[On('open-capture-results')]
    public function open(string $planCode, ?string $phone = null): void
    {
        $this->planCode = $planCode;
        $this->customerPhone = $phone;
        $this->isSuccess = false;
        $this->pdfBase64 = null;
        $this->errorMessage = '';
        $this->isOpen = true;
        $this->isLoading = true;
    }

    public function loadResultsData(FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->isLoading = true;
        $this->errorMessage = '';

        try {
            $entity = $planRepo->getPlanByCode($this->planCode);

            if ($entity) {
                $this->version = $entity->version ?? 1;
                $this->customerName = $entity->customerName;
                $this->costoTotal = round($entity->calculateTotalPlanCostPerHectare(), 2);
                $this->costoTradicional = round($this->costoTotal * 1.15, 2);
                $this->dosis = 1.0;
                $this->rendimiento = round($entity->targetYield ?? 12.0, 1);
                $this->rendimientoTradicional = round(($entity->targetYield ?? 10.0) * 0.85, 1);
                $this->precioCosecha = 5500.0;
                $this->fechaCosecha = date('Y-m-d');
                $this->recomendaciones = 'Mantener monitoreo de humedad y balance nutricional.';
                $this->propuesta = 'Programa nutricional integral Grupo Impulsora.';
                $this->buenasPracticas = 'Calibración de boquillas y fertirriego programado.';
                $this->coordenadas = ($entity->latitude && $entity->longitude)
                    ? "{$entity->latitude},{$entity->longitude}"
                    : '25.790466,-108.995888';
            }

            // Cargar datos previos si ya existía una captura de resultados
            $existing = $planRepo->getResultsPdfData($this->planCode);
            if (! empty($existing['planInfo'])) {
                $info = $existing['planInfo'];
                if (! empty($info['U_FechaCaptura'])) {
                    $this->fechaCosecha = substr((string) $info['U_FechaCaptura'], 0, 10);
                }
                if (isset($info['RendimientoObtenido']) && (float) $info['RendimientoObtenido'] > 0) {
                    $this->rendimiento = (float) $info['RendimientoObtenido'];
                } elseif (isset($info['U_Rendimiento']) && (float) $info['U_Rendimiento'] > 0) {
                    $this->rendimiento = (float) $info['U_Rendimiento'];
                }
                if (isset($info['U_CostoTotal']) && (float) $info['U_CostoTotal'] > 0) {
                    $this->costoTotal = (float) $info['U_CostoTotal'];
                }
                if (isset($info['U_Costo_Trad']) && (float) $info['U_Costo_Trad'] > 0) {
                    $this->costoTradicional = (float) $info['U_Costo_Trad'];
                }
                if (isset($info['U_Dosis']) && (float) $info['U_Dosis'] > 0) {
                    $this->dosis = (float) $info['U_Dosis'];
                }
                if (isset($info['U_Rendimiento_Trad']) && (float) $info['U_Rendimiento_Trad'] > 0) {
                    $this->rendimientoTradicional = (float) $info['U_Rendimiento_Trad'];
                }
                if (isset($info['U_Precio_Cosecha']) && (float) $info['U_Precio_Cosecha'] > 0) {
                    $this->precioCosecha = (float) $info['U_Precio_Cosecha'];
                }
                if (! empty($info['U_Recomendaciones'])) {
                    $this->recomendaciones = (string) $info['U_Recomendaciones'];
                }
                if (! empty($info['U_Propuesta'])) {
                    $this->propuesta = (string) $info['U_Propuesta'];
                }
                if (! empty($info['U_BuenasPracticas'])) {
                    $this->buenasPracticas = (string) $info['U_BuenasPracticas'];
                }
                if (! empty($info['U_Coordenadas'])) {
                    $this->coordenadas = (string) $info['U_Coordenadas'];
                }
            }

            $this->recalculateMetrics();
        } catch (\Throwable $e) {
            Log::warning('[CaptureResultsModal] Error loading results data', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }

        Log::info('[CaptureResultsModal] Modal data loaded', [
            'planCode' => $this->planCode,
            'version' => $this->version,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
        ]);
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'rendimiento' && (float) $this->rendimiento > 99.9) {
            $this->addError('rendimiento', 'El rendimiento no puede ser mayor a 99.9 Ton/Ha.');
        }

        if (in_array($propertyName, ['rendimiento', 'rendimientoTradicional', 'precioCosecha', 'costoTotal', 'costoTradicional'], true)) {
            $this->recalculateMetrics();
        }
    }

    public function recalculateMetrics(): void
    {
        $r = (float) ($this->rendimiento ?? 0);
        $rt = (float) ($this->rendimientoTradicional ?? 0);
        $p = (float) ($this->precioCosecha ?? 0);
        $c = (float) ($this->costoTotal ?? 0);
        $ct = (float) ($this->costoTradicional ?? 0);

        $this->utilidadExtra = round(($r - $rt) * $p, 2);
        $this->inversionExtra = round($c - $ct, 2);
        $this->retornoInversion = $this->inversionExtra != 0.0 ? round($this->utilidadExtra / $this->inversionExtra, 2) : 0.0;
    }

    public function setCoordinates(float $lat, float $lng): void
    {
        $this->coordenadas = number_format($lat, 6, '.', '').','.number_format($lng, 6, '.', '');
        $this->resetErrorBag('coordenadas');
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->isLoading = false;
        $this->isSuccess = false;
        $this->errorMessage = '';
        $this->pdfBase64 = null;
    }

    public function saveResults(
        CaptureResultsUseCase $useCase,
        GenerateResultsPdfUseCase $pdfUseCase,
        FertilizationPlanRepositoryInterface $planRepo
    ): void {
        $this->validate([
            'fechaCosecha' => 'required|date',
            'rendimiento' => 'required|numeric|min:0.1|max:99.9',
            'rendimientoTradicional' => 'required|numeric|min:0',
            'dosis' => 'required|numeric|min:0',
            'precioCosecha' => 'required|numeric|min:0',
            'costoTotal' => 'required|numeric|min:0',
            'costoTradicional' => 'required|numeric|min:0',
            'recomendaciones' => 'required|string|max:500',
            'propuesta' => 'required|string|max:500',
            'buenasPracticas' => 'required|string|max:500',
            'coordenadas' => 'required|string|max:500',
        ], [
            'fechaCosecha.required' => 'La fecha de cosecha es obligatoria.',
            'rendimiento.required' => 'El rendimiento es obligatorio.',
            'rendimiento.numeric' => 'Número inválido.',
            'rendimiento.min' => 'El rendimiento debe ser mayor a 0 Ton/Ha.',
            'rendimiento.max' => 'El rendimiento no puede ser mayor a 99.9 Ton/Ha.',
            'rendimientoTradicional.required' => 'El rendimiento tradicional es obligatorio.',
            'dosis.required' => 'La dosis es obligatoria.',
            'precioCosecha.required' => 'El precio cosecha es obligatorio.',
            'costoTotal.required' => 'El costo total es obligatorio.',
            'costoTradicional.required' => 'El costo tradicional es obligatorio.',
            'recomendaciones.required' => 'Las recomendaciones son obligatorias.',
            'propuesta.required' => 'La propuesta es obligatoria.',
            'buenasPracticas.required' => 'Las buenas prácticas son obligatorias.',
            'coordenadas.required' => 'La ubicación de la parcela es obligatoria.',
        ]);

        $this->isSaving = true;
        $this->errorMessage = '';
        $startTime = microtime(true);

        Log::info('[CaptureResultsModal] Saving final results', [
            'planCode' => $this->planCode,
            'rendimiento' => $this->rendimiento,
            'costoTotal' => $this->costoTotal,
            'utilidadExtra' => $this->utilidadExtra,
            'retornoInversion' => $this->retornoInversion,
        ]);

        try {
            $dto = new CaptureResultDTO(
                planCode: $this->planCode,
                finalYieldTonPerHa: (float) $this->rendimiento,
                costPerHectare: (float) $this->costoTotal,
                traditionalCost: (float) $this->costoTradicional,
                harvestDate: $this->fechaCosecha,
                dose: (float) $this->dosis,
                traditionalYield: (float) $this->rendimientoTradicional,
                harvestPrice: (float) $this->precioCosecha,
                extraProfit: (float) $this->utilidadExtra,
                extraInvestment: (float) $this->inversionExtra,
                returnOnInvestment: (float) $this->retornoInversion,
                recommendations: $this->recomendaciones,
                proposal: $this->propuesta,
                goodPractices: $this->buenasPracticas,
                coordinates: $this->coordenadas,
            );

            $result = $useCase->execute($dto);

            if (! ($result['success'] ?? false)) {
                $this->errorMessage = $result['message'] ?? 'Error al guardar resultados en el servidor.';
                $this->isSaving = false;
                Log::warning('[CaptureResultsModal] Save results rejected by API', [
                    'planCode' => $this->planCode,
                    'result' => $result,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return;
            }

            // Generar y almacenar PDF de resultados en base64
            try {
                $pdfResult = $pdfUseCase->execute($this->planCode);
                $this->pdfBase64 = is_array($pdfResult) ? ($pdfResult['pdfBase64'] ?? null) : base64_encode($pdfResult);
                if (! empty($this->pdfBase64)) {
                    $planRepo->saveResultsPdf($this->planCode, $this->version, 'Resultados', $this->pdfBase64);
                }
                Log::info('[CaptureResultsModal] Results PDF generated and stored in DB', [
                    'planCode' => $this->planCode,
                    'version' => $this->version,
                    'bytes' => strlen($this->pdfBase64 ?? ''),
                ]);
            } catch (\Throwable $pdfError) {
                Log::warning('[CaptureResultsModal] Results PDF generation fallback error', [
                    'planCode' => $this->planCode,
                    'error' => $pdfError->getMessage(),
                ]);
            }

            $this->isSuccess = true;
            Log::info('[CaptureResultsModal] Final results saved successfully', [
                'planCode' => $this->planCode,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('plan-results-updated');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Dictamen de resultados guardado exitosamente.',
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error: '.$e->getMessage();
            Log::error('[CaptureResultsModal] Exception saving final results', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } finally {
            $this->isSaving = false;
        }
    }

    public function downloadPdf(GenerateResultsPdfUseCase $pdfUseCase)
    {
        $startTime = microtime(true);
        Log::info('[CaptureResultsModal] Download results PDF initiated', [
            'planCode' => $this->planCode,
            'cached' => ! empty($this->pdfBase64),
        ]);

        try {
            $pdfContent = null;
            if (! empty($this->pdfBase64)) {
                $pdfContent = base64_decode($this->pdfBase64);
            } else {
                $pdfResult = $pdfUseCase->execute($this->planCode);
                $base64 = is_array($pdfResult) ? ($pdfResult['pdfBase64'] ?? null) : base64_encode($pdfResult);
                $this->pdfBase64 = $base64;
                $pdfContent = ! empty($base64) ? base64_decode($base64) : null;
            }

            if (empty($pdfContent)) {
                throw new \RuntimeException('No se pudo generar el contenido del PDF de resultados.');
            }

            Log::info('[CaptureResultsModal] Results PDF download prepared', [
                'planCode' => $this->planCode,
                'bytes' => strlen($pdfContent),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return response()->streamDownload(
                function () use ($pdfContent) {
                    echo $pdfContent;
                },
                "Dictamen-Resultados-{$this->planCode}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            Log::error('[CaptureResultsModal] Results PDF download failed', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al descargar PDF: '.$e->getMessage(),
            ]);
        }
    }

    public function getIsCustomerPhoneMissingProperty(): bool
    {
        $phone = preg_replace('/\D/', '', (string) $this->customerPhone);

        return strlen($phone) < 10;
    }

    public function shareWhatsApp(WhatsAppServiceInterface $whatsApp): void
    {
        $phone = preg_replace('/\D/', '', (string) $this->customerPhone);
        if (strlen($phone) < 10) {
            Log::warning('[CaptureResultsModal] WhatsApp share blocked: phone is missing', [
                'planCode' => $this->planCode,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede compartir: El cliente no cuenta con número celular registrado en SAP.',
            ]);

            return;
        }

        $this->isSharing = true;
        $startTime = microtime(true);

        Log::info('[CaptureResultsModal] WhatsApp share initiated', [
            'planCode' => $this->planCode,
            'customerPhone' => $phone,
            'cached' => ! empty($this->pdfBase64),
        ]);

        try {
            $message = "Hola {$this->customerName} 👋\nYa tengo lista la captura de resultados de tu Plan de Fertilización #{$this->planCode}.\n📊 Aquí podrás revisar cómo se aplicó el programa, los costos y el rendimiento final obtenido.";

            $msgDto = new WhatsAppMessageDTO(
                recipientPhone: $phone,
                customerName: $this->customerName,
                documentBase64: (string) $this->pdfBase64,
                caption: $message,
                planCode: $this->planCode,
            );

            $result = $whatsApp->sendPlanDocument($msgDto);

            if ($result) {
                Log::info('[CaptureResultsModal] WhatsApp share succeeded', [
                    'planCode' => $this->planCode,
                    'customerPhone' => $phone,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Dictamen de resultados compartido vía WhatsApp exitosamente.',
                ]);
            } else {
                Log::warning('[CaptureResultsModal] WhatsApp share failed via UltraMSG', [
                    'planCode' => $this->planCode,
                    'customerPhone' => $phone,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se pudo enviar el documento vía WhatsApp. Verifica el número telefónico.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[CaptureResultsModal] WhatsApp share exception', [
                'planCode' => $this->planCode,
                'customerPhone' => $phone,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al compartir: '.$e->getMessage(),
            ]);
        } finally {
            $this->isSharing = false;
        }
    }

    public function render()
    {
        return view('livewire.results.capture-results-modal');
    }
}

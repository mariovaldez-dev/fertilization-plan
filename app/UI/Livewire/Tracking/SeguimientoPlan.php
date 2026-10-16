<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

final class SeguimientoPlan extends Component
{
    public string $planCode = '';

    #[Url(as: 'cultivoId')]
    public ?int $cultivoId = null;

    public string $nombreCultivo = '';

    public string $nombrePlan = '';

    public array $plan = [];

    public array $etapas = [];

    public array $productosList = [];

    public array $metodosAplicacion = [];

    public bool $isLoading = true;

    public bool $isSaving = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public bool $showSuccessModal = false;

    public function mount(
        string $planCode,
        FertilizationPlanRepositoryInterface $planRepo,
        CatalogsRepositoryInterface $catalogs,
        ?int $cultivoId = null
    ): void {
        $this->planCode = $planCode;
        if ($cultivoId !== null) {
            $this->cultivoId = $cultivoId;
        }

        $this->isLoading = true;

        if (app()->runningUnitTests()) {
            $this->productosList = $catalogs->getProducts();
            $this->metodosAplicacion = $catalogs->getApplicationMethods();
            $this->cargarDetallePlan($planRepo, $catalogs);
        }
    }

    public function cargarDetallePlan(
        ?FertilizationPlanRepositoryInterface $planRepo = null,
        ?CatalogsRepositoryInterface $catalogs = null
    ): void {
        $planRepo = $planRepo ?? app(FertilizationPlanRepositoryInterface::class);
        $catalogs = $catalogs ?? app(CatalogsRepositoryInterface::class);

        $this->isLoading = true;
        $this->errorMessage = '';

        if (empty($this->productosList)) {
            $this->productosList = $catalogs->getProducts();
        }
        if (empty($this->metodosAplicacion)) {
            $this->metodosAplicacion = $catalogs->getApplicationMethods();
        }

        $startTime = microtime(true);

        Log::info('[SeguimientoPlan] Loading tracking detail', [
            'planCode' => $this->planCode,
            'cultivoId' => $this->cultivoId,
        ]);

        try {
            $planEntity = $planRepo->getPlanByCode($this->planCode);
            if ($planEntity) {
                $this->plan = [
                    'planCode' => $planEntity->planCode,
                    'customerName' => $planEntity->customerName,
                    'currentCropName' => $planEntity->currentCropName,
                    'hectares' => $planEntity->hectares,
                    'season' => $planEntity->season,
                ];

                $this->nombrePlan = $planEntity->customerName;
                $this->nombreCultivo = $planEntity->currentCropName ?? '';

                if (empty($this->cultivoId) && ! empty($planEntity->currentCropCode)) {
                    $this->cultivoId = (int) $planEntity->currentCropCode;
                }
            }

            // 1. Intentar cargar etapas existentes de seguimiento vía SP pf_sp_obtener_seguimiento_etapas
            $rawStages = [];
            if (! empty($this->cultivoId)) {
                $rawStages = $planRepo->getTrackingStages($this->planCode, $this->cultivoId);
            }

            if (! empty($rawStages)) {
                $this->etapas = [];
                foreach ($rawStages as $st) {
                    $productosArray = [];
                    if (isset($st['productos'])) {
                        if (is_string($st['productos'])) {
                            $productosArray = json_decode($st['productos'], true) ?: [];
                        } elseif (is_array($st['productos'])) {
                            $productosArray = $st['productos'];
                        }
                    }

                    $cleanProducts = [];
                    foreach ($productosArray as $p) {
                        $rawProveedor = $p['Proveedor'] ?? $p['proveedor'] ?? '';
                        $esImpulsora = strtoupper(trim((string) $rawProveedor)) === 'GRUPO IMPULSORA' || empty($rawProveedor);
                        $rawId = (string) ($p['id'] ?? $p['ID'] ?? $p['idProducto'] ?? '');

                        $cleanProducts[] = [
                            'idProducto' => $esImpulsora ? $rawId : '',
                            'nombreProducto' => ! $esImpulsora ? ($rawId ?: ($p['nombreProducto'] ?? '')) : '',
                            'dosis' => (float) ($p['dosis'] ?? 0.0),
                            'precio' => (float) ($p['precio'] ?? $p['Precio'] ?? 0.0),
                            'Proveedor' => $esImpulsora ? 'GRUPO IMPULSORA' : (string) $rawProveedor,
                            'tipo' => $esImpulsora ? 'impulsora' : 'otros',
                        ];
                    }

                    $fecha = ! empty($st['fechaAplicacion'])
                        ? explode('T', (string) $st['fechaAplicacion'])[0]
                        : date('Y-m-d');

                    $this->etapas[] = [
                        'codeEtapa' => (int) ($st['idEtapa'] ?? $st['codeEtapa'] ?? 0),
                        'idEtapa' => (int) ($st['idEtapa'] ?? $st['codeEtapa'] ?? 0),
                        'nombreEtapa' => (string) ($st['nombreEtapa'] ?? $st['nameEtapa'] ?? ''),
                        'idAplicacion' => $st['idAplicacion'] ?? '',
                        'idMetodo' => $st['idAplicacion'] ?? '',
                        'fechaAplicacion' => $fecha,
                        'comentario' => (string) ($st['comentario'] ?? ''),
                        'productos' => $cleanProducts,
                    ];
                }
            } elseif ($planEntity && ! empty($planEntity->stages)) {
                // 2. Si no hay registros previos, inicializar con las etapas del plan
                $this->etapas = [];
                foreach ($planEntity->stages as $st) {
                    $prods = [];
                    foreach ($st->products as $p) {
                        $prods[] = [
                            'tipo' => $p->isImpulsoraProduct ? 'impulsora' : 'otros',
                            'idProducto' => $p->isImpulsoraProduct ? $p->itemCode : '',
                            'nombreProducto' => ! $p->isImpulsoraProduct ? $p->itemName : '',
                            'dosis' => $p->dosePerHectare,
                            'precio' => $p->unitPrice,
                            'Proveedor' => $p->isImpulsoraProduct ? 'GRUPO IMPULSORA' : '',
                        ];
                    }

                    $this->etapas[] = [
                        'codeEtapa' => $st->stageId,
                        'idEtapa' => $st->stageId,
                        'nombreEtapa' => $st->stageName,
                        'idAplicacion' => '1',
                        'idMetodo' => '1',
                        'fechaAplicacion' => date('Y-m-d'),
                        'comentario' => '',
                        'productos' => $prods,
                    ];
                }
            }

            Log::info('[SeguimientoPlan] Plan detail loaded', [
                'planCode' => $this->planCode,
                'stages_count' => count($this->etapas),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al cargar el plan: '.$e->getMessage();
            Log::error('[SeguimientoPlan] Error loading plan detail', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function updated($propertyName, $value): void
    {
        // Sincronización reactiva al cambiar tipo de producto (Impulsora vs Otros)
        if (preg_match('/etapas\.(\d+)\.productos\.(\d+)\.tipo/', $propertyName, $matches)) {
            $eIndex = (int) $matches[1];
            $pIndex = (int) $matches[2];

            if ($value === 'otros') {
                $this->etapas[$eIndex]['productos'][$pIndex]['idProducto'] = '';
                $this->etapas[$eIndex]['productos'][$pIndex]['nombreProducto'] = '';
                $this->etapas[$eIndex]['productos'][$pIndex]['Proveedor'] = '';
                $this->etapas[$eIndex]['productos'][$pIndex]['precio'] = 0.0;
            } else {
                $this->etapas[$eIndex]['productos'][$pIndex]['idProducto'] = '';
                $this->etapas[$eIndex]['productos'][$pIndex]['nombreProducto'] = '';
                $this->etapas[$eIndex]['productos'][$pIndex]['Proveedor'] = 'GRUPO IMPULSORA';
                $this->etapas[$eIndex]['productos'][$pIndex]['precio'] = 0.0;
            }
        }
    }

    public function seleccionarProductoImpulsora(int $etapaIndex, int $productoIndex, string $itemCode): void
    {
        $this->etapas[$etapaIndex]['productos'][$productoIndex]['idProducto'] = $itemCode;
        foreach ($this->productosList as $p) {
            $code = (string) ($p['ItemCode'] ?? $p['itemCode'] ?? '');
            if ($code === $itemCode) {
                $this->etapas[$etapaIndex]['productos'][$productoIndex]['nombreProducto'] = (string) ($p['ItemName'] ?? $p['name'] ?? '');
                break;
            }
        }
    }

    public function agregarProducto(int $etapaIndex): void
    {
        $this->etapas[$etapaIndex]['productos'][] = [
            'tipo' => 'impulsora',
            'idProducto' => '',
            'nombreProducto' => '',
            'dosis' => 0.0,
            'precio' => 0.0,
            'Proveedor' => 'GRUPO IMPULSORA',
        ];
    }

    public function eliminarProducto(int $etapaIndex, int $productoIndex): void
    {
        unset($this->etapas[$etapaIndex]['productos'][$productoIndex]);
        $this->etapas[$etapaIndex]['productos'] = array_values($this->etapas[$etapaIndex]['productos']);
    }

    public function eliminarEtapa(int $etapaIndex): void
    {
        unset($this->etapas[$etapaIndex]);
        $this->etapas = array_values($this->etapas);
    }

    public function guardarSeguimiento(FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->isSaving = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        if (empty($this->etapas)) {
            $this->errorMessage = 'No hay etapas para guardar en el seguimiento.';
            $this->isSaving = false;

            return;
        }

        // Validaciones idénticas al proyecto React (SeguimientoPlan.jsx)
        foreach ($this->etapas as $st) {
            if (empty($st['productos'])) {
                $this->errorMessage = "Debe agregar al menos un producto en la etapa: \"{$st['nombreEtapa']}\".";
                $this->isSaving = false;

                return;
            }

            if (empty($st['fechaAplicacion'])) {
                $this->errorMessage = "Falta la fecha de aplicación en la etapa: \"{$st['nombreEtapa']}\".";
                $this->isSaving = false;

                return;
            }

            $idApp = $st['idAplicacion'] ?? $st['idMetodo'] ?? '';
            if (trim((string) $idApp) === '') {
                $this->errorMessage = "Falta seleccionar el método de aplicación en la etapa: \"{$st['nombreEtapa']}\".";
                $this->isSaving = false;

                return;
            }

            foreach ($st['productos'] as $p) {
                $pTipo = $p['tipo'] ?? 'impulsora';
                $prodNombre = ($p['nombreProducto'] ?? '') ?: (($p['idProducto'] ?? '') ?: 'Producto sin nombre');

                if ($pTipo === 'impulsora' && empty($p['idProducto'])) {
                    $this->errorMessage = "Falta seleccionar un producto \"Impulsora\" en la etapa: \"{$st['nombreEtapa']}\".";
                    $this->isSaving = false;

                    return;
                }

                if ($pTipo === 'otros' && empty(trim((string) $p['nombreProducto']))) {
                    $this->errorMessage = "Falta capturar el nombre del producto \"Otros\" en la etapa: \"{$st['nombreEtapa']}\".";
                    $this->isSaving = false;

                    return;
                }

                $precioNum = (float) ($p['precio'] ?? 0);
                if (trim((string) ($p['precio'] ?? '')) === '' || $precioNum < 0) {
                    $this->errorMessage = "Falta capturar un precio válido para el producto \"{$prodNombre}\" en la etapa: \"{$st['nombreEtapa']}\".";
                    $this->isSaving = false;

                    return;
                }

                $dosis = (float) ($p['dosis'] ?? 0);
                if ($dosis <= 0) {
                    $this->errorMessage = "La dosis para el producto \"{$prodNombre}\" en la etapa: \"{$st['nombreEtapa']}\" debe ser mayor a cero.";
                    $this->isSaving = false;

                    return;
                }
            }
        }

        $startTime = microtime(true);
        Log::info('[SeguimientoPlan] Saving tracking stages', [
            'planCode' => $this->planCode,
            'stages_count' => count($this->etapas),
        ]);

        try {
            $dto = new UpdateTrackingDTO(
                planCode: $this->planCode,
                stages: $this->etapas
            );

            $result = $planRepo->saveTracking($dto);

            if (! empty($result['success']) && $result['success']) {
                $this->successMessage = $result['message'] ?? '¡Seguimiento guardado exitosamente!';
                $this->showSuccessModal = true;
                Log::info('[SeguimientoPlan] Tracking stages saved successfully', [
                    'planCode' => $this->planCode,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => $this->successMessage,
                ]);
            } else {
                $this->errorMessage = $result['message'] ?? 'Error al guardar el seguimiento.';
                Log::warning('[SeguimientoPlan] Tracking save returned error', [
                    'planCode' => $this->planCode,
                    'result' => $result,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error de conexión: '.$e->getMessage();
            Log::error('[SeguimientoPlan] Exception saving tracking stages', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } finally {
            $this->isSaving = false;
        }
    }

    public function getTotalProductsCountProperty(): int
    {
        $count = 0;
        foreach ($this->etapas as $st) {
            $count += count($st['productos'] ?? []);
        }

        return $count;
    }

    public function getTotalStagesCountProperty(): int
    {
        return count($this->etapas);
    }

    public function render()
    {
        return view('livewire.tracking.seguimiento-plan')
            ->layout('layouts.app', ['title' => 'Registro de Seguimiento']);
    }
}

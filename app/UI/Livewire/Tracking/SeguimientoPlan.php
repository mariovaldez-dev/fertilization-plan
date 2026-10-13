<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
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

    public bool $isLoading = false;

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

        $this->productosList = $catalogs->getProducts();
        $this->metodosAplicacion = $catalogs->getApplicationMethods();
        $this->cargarDetallePlan($planRepo);
    }

    public function cargarDetallePlan(FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->isLoading = true;
        $this->errorMessage = '';

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
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al cargar el plan: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
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

        // Validaciones idénticas al proyecto React
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
                $prodNombre = $p['nombreProducto'] ?: ($p['idProducto'] ?: 'Producto');

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

                $dosis = (float) ($p['dosis'] ?? 0);
                if ($dosis <= 0) {
                    $this->errorMessage = "La dosis para el producto \"{$prodNombre}\" en la etapa: \"{$st['nombreEtapa']}\" debe ser mayor a cero.";
                    $this->isSaving = false;

                    return;
                }
            }
        }

        try {
            $dto = new UpdateTrackingDTO(
                planCode: $this->planCode,
                stages: $this->etapas
            );

            $result = $planRepo->saveTracking($dto);

            if (! empty($result['success']) && $result['success']) {
                $this->successMessage = $result['message'] ?? '¡Seguimiento guardado exitosamente!';
                $this->showSuccessModal = true;
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => $this->successMessage,
                ]);
            } else {
                $this->errorMessage = $result['message'] ?? 'Error al guardar el seguimiento.';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error de conexión: '.$e->getMessage();
        } finally {
            $this->isSaving = false;
        }
    }

    public function render()
    {
        return view('livewire.tracking.seguimiento-plan')
            ->layout('layouts.app', ['title' => 'Edición de Seguimiento']);
    }
}

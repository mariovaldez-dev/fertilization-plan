<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Livewire\Component;

final class SeguimientoPlan extends Component
{
    public string $planCode = '';

    public array $plan = [];

    public array $etapas = [];

    public array $productosList = [];

    public array $metodosAplicacion = [];

    public bool $isLoading = false;

    public bool $isSaving = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(
        string $planCode,
        FertilizationPlanRepositoryInterface $planRepo,
        CatalogsRepositoryInterface $catalogs
    ): void {
        $this->planCode = $planCode;
        $this->productosList = $catalogs->getProducts();
        $this->metodosAplicacion = $catalogs->getApplicationMethods();
        $this->cargarDetallePlan($planRepo);
    }

    public function cargarDetallePlan(FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->isLoading = true;
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

                $this->etapas = [];
                foreach ($planEntity->stages as $st) {
                    $prods = [];
                    foreach ($st->products as $p) {
                        $prods[] = [
                            'tipo' => $p->isImpulsoraProduct ? 'impulsora' : 'otros',
                            'idProducto' => $p->itemCode,
                            'nombreProducto' => $p->itemName,
                            'dosis' => $p->dosePerHectare,
                            'dosisReal' => $p->dosePerHectare,
                            'unidad' => $p->unitOfMeasure,
                            'precio' => $p->unitPrice,
                        ];
                    }

                    $this->etapas[] = [
                        'idEtapa' => $st->stageId,
                        'nombreEtapa' => $st->stageName,
                        'idMetodo' => $st->applicationMethod ?? 'Fertirriego',
                        'fechaAplicacion' => date('Y-m-d'),
                        'comentarios' => '',
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
            'dosisReal' => 0.0,
            'unidad' => 'Kg/Ha',
            'precio' => 0.0,
        ];
    }

    public function eliminarProducto(int $etapaIndex, int $productoIndex): void
    {
        unset($this->etapas[$etapaIndex]['productos'][$productoIndex]);
        $this->etapas[$etapaIndex]['productos'] = array_values($this->etapas[$etapaIndex]['productos']);
    }

    public function guardarSeguimiento(FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->isSaving = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            $dto = new UpdateTrackingDTO(
                planCode: $this->planCode,
                records: $this->etapas
            );

            $result = $planRepo->saveTracking($dto);

            if (! empty($result['success']) && $result['success']) {
                $this->successMessage = '¡Seguimiento guardado exitosamente!';
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

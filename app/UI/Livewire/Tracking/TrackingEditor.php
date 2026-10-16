<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Application\UseCases\Plans\SaveTrackingUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

final class TrackingEditor extends Component
{
    public string $planCode = '';

    public ?int $version = 1;

    public array $plan = [];

    public array $stages = [];

    public array $availableProducts = [];

    public bool $isReadOnly = false;

    public function mount(
        string $planCode,
        FertilizationPlanRepositoryInterface $planRepo,
        CatalogsRepositoryInterface $catalogs
    ): void {
        $startTime = microtime(true);
        $this->planCode = $planCode;
        $entity = $planRepo->getPlanByCode($planCode);

        if ($entity) {
            $this->isReadOnly = $entity->isReadOnly();
            $this->plan = [
                'planCode' => $entity->planCode,
                'version' => $entity->version,
                'customerName' => $entity->customerName,
                'advisorName' => $entity->advisorName,
                'crop' => $entity->currentCropName,
                'hectares' => $entity->hectares,
                'status' => $entity->status->label(),
                'hasTestigo' => $entity->hasTestigo,
            ];

            $this->stages = array_map(fn ($stage) => $stage->toArray(), $entity->stages);
        }

        $this->availableProducts = $catalogs->getProducts();

        Log::info('[TrackingEditor] Component mounted', [
            'planCode' => $planCode,
            'stages_count' => count($this->stages),
            'isReadOnly' => $this->isReadOnly,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);
    }

    public function addImpulsoraProduct(int $stageIndex): void
    {
        $this->stages[$stageIndex]['impulsoraProducts'][] = [
            'itemCode' => '',
            'itemName' => '',
            'dosePerHectare' => 0.0,
            'unitOfMeasure' => 'Kg/Ha',
            'unitPrice' => 0.0,
            'applicationMethod' => 'Fertirriego',
            'isImpulsoraProduct' => true,
        ];
    }

    public function addOtherProduct(int $stageIndex): void
    {
        $this->stages[$stageIndex]['otherProducts'][] = [
            'itemCode' => '',
            'itemName' => '',
            'dosePerHectare' => 0.0,
            'unitOfMeasure' => 'Kg/Ha',
            'unitPrice' => 0.0,
            'applicationMethod' => 'Fertirriego',
            'isImpulsoraProduct' => false,
        ];
    }

    public function removeImpulsoraProduct(int $stageIndex, int $productIndex): void
    {
        unset($this->stages[$stageIndex]['impulsoraProducts'][$productIndex]);
        $this->stages[$stageIndex]['impulsoraProducts'] = array_values($this->stages[$stageIndex]['impulsoraProducts']);
    }

    public function removeOtherProduct(int $stageIndex, int $productIndex): void
    {
        unset($this->stages[$stageIndex]['otherProducts'][$productIndex]);
        $this->stages[$stageIndex]['otherProducts'] = array_values($this->stages[$stageIndex]['otherProducts']);
    }

    public function saveTracking(SaveTrackingUseCase $useCase): void
    {
        if ($this->isReadOnly) {
            Log::warning('[TrackingEditor] Save tracking blocked: plan is read-only', [
                'planCode' => $this->planCode,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Este plan está cerrado o en modo solo lectura.',
            ]);

            return;
        }

        $startTime = microtime(true);
        Log::info('[TrackingEditor] Saving tracking stages', [
            'planCode' => $this->planCode,
            'stages_count' => count($this->stages),
        ]);

        try {
            $dto = new UpdateTrackingDTO(
                planCode: $this->planCode,
                isTestigo: false,
                stages: $this->stages,
            );

            $result = $useCase->execute($dto);

            if (! empty($result['estado']) && $result['estado'] === 1) {
                Log::warning('[TrackingEditor] Tracking save error response', [
                    'planCode' => $this->planCode,
                    'result' => $result,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => $result['mensaje'] ?? 'Error al guardar el seguimiento.',
                ]);

                return;
            }

            Log::info('[TrackingEditor] Tracking saved successfully', [
                'planCode' => $this->planCode,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Seguimiento guardado correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[TrackingEditor] Exception saving tracking', [
                'planCode' => $this->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tracking.tracking-editor')->layout('layouts.app');
    }
}

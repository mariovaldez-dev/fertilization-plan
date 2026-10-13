<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Application\UseCases\Plans\SaveTrackingUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Livewire\Component;

final class TestigoTrackingEditor extends Component
{
    public string $planCode = '';

    public array $plan = [];

    public array $stages = [];

    public array $availableProducts = [];

    public bool $isReadOnly = false;

    public function mount(
        string $planCode,
        FertilizationPlanRepositoryInterface $planRepo,
        CatalogsRepositoryInterface $catalogs
    ): void {
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
    }

    public function addProduct(int $stageIndex): void
    {
        $this->stages[$stageIndex]['otherProducts'][] = [
            'itemCode' => '',
            'itemName' => '',
            'dosePerHectare' => 0.0,
            'unitOfMeasure' => 'Kg/Ha',
            'unitPrice' => 0.0,
            'applicationMethod' => 'Tradicional',
            'isImpulsoraProduct' => false,
        ];
    }

    public function removeProduct(int $stageIndex, int $productIndex): void
    {
        unset($this->stages[$stageIndex]['otherProducts'][$productIndex]);
        $this->stages[$stageIndex]['otherProducts'] = array_values($this->stages[$stageIndex]['otherProducts']);
    }

    public function saveTestigoTracking(SaveTrackingUseCase $useCase): void
    {
        if ($this->isReadOnly) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Este plan está cerrado o en modo solo lectura.',
            ]);

            return;
        }

        $dto = new UpdateTrackingDTO(
            planCode: $this->planCode,
            isTestigo: true,
            stages: $this->stages,
        );

        $result = $useCase->execute($dto);

        if (! empty($result['estado']) && $result['estado'] === 1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['mensaje'] ?? 'Error al guardar el seguimiento del testigo.',
            ]);

            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Seguimiento de lote Testigo guardado correctamente.',
        ]);
    }

    public function render()
    {
        return view('livewire.tracking.testigo-tracking-editor')->layout('layouts.app');
    }
}

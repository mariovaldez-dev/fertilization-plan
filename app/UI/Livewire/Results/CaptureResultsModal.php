<?php

declare(strict_types=1);

namespace App\UI\Livewire\Results;

use App\Application\UseCases\Plans\CaptureResultsUseCase;
use App\Domain\FertilizationPlan\DTOs\CaptureResultDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Livewire\Attributes\On;
use Livewire\Component;

final class CaptureResultsModal extends Component
{
    public bool $isOpen = false;

    public string $planCode = '';

    public string $customerName = '';

    public float $finalYieldTonPerHa = 0.0;

    public ?float $testigoYieldTonPerHa = null;

    public float $costPerHectare = 0.0;

    public float $traditionalCost = 0.0;

    public ?string $harvestDate = null;

    public ?string $agronomicConclusions = null;

    #[On('open-capture-results')]
    public function open(string $planCode, FertilizationPlanRepositoryInterface $planRepo): void
    {
        $this->planCode = $planCode;
        $entity = $planRepo->getPlanByCode($planCode);

        if ($entity) {
            $this->customerName = $entity->customerName;
            $this->costPerHectare = $entity->calculateTotalPlanCostPerHectare();
            $this->traditionalCost = round($this->costPerHectare * 1.25, 2); // Default estimate or SP value
            $this->harvestDate = date('Y-m-d');
        }

        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function saveResults(CaptureResultsUseCase $useCase): void
    {
        $this->validate([
            'finalYieldTonPerHa' => 'required|numeric|min:0.01',
            'harvestDate' => 'required|date',
        ]);

        $dto = new CaptureResultDTO(
            planCode: $this->planCode,
            finalYieldTonPerHa: $this->finalYieldTonPerHa,
            costPerHectare: $this->costPerHectare,
            traditionalCost: $this->traditionalCost,
            testigoYieldTonPerHa: $this->testigoYieldTonPerHa,
            harvestDate: $this->harvestDate,
            agronomicConclusions: $this->agronomicConclusions,
        );

        $result = $useCase->execute($dto);

        if (! empty($result['estado']) && $result['estado'] === 1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['mensaje'] ?? 'Error al registrar resultados agronómicos.',
            ]);

            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resultados agronómicos registrados correctamente.',
        ]);

        $this->isOpen = false;
        $this->dispatch('plan-results-updated');
    }

    public function render()
    {
        return view('livewire.results.capture-results-modal');
    }
}

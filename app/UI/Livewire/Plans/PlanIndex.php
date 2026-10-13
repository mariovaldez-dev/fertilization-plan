<?php

declare(strict_types=1);

namespace App\UI\Livewire\Plans;

use App\Application\UseCases\Plans\CancelPlanUseCase;
use App\Application\UseCases\Plans\VersionPlanUseCase;
use App\Application\UseCases\Queries\GetCustomerPlansUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class PlanIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $searchQuery = '';

    #[Url(as: 'zona')]
    public string $selectedZone = '';

    #[Url(as: 'territorio')]
    public string $selectedTerritory = '';

    #[Url(as: 'temporada')]
    public string $selectedSeason = '2025-2026';

    public bool $onlyClosed = false;

    public array $zones = [];

    public array $territories = [];

    public array $seasons = [
        '2025-2026' => 'Ciclo 2025 - 2026',
        '2024-2025' => 'Ciclo 2024 - 2025',
        '2023-2024' => 'Ciclo 2023 - 2024',
    ];

    public ?string $cardCode = null;

    public function mount(CatalogsRepositoryInterface $catalogs, ?string $cardCode = null): void
    {
        $this->cardCode = $cardCode;
        $this->zones = $catalogs->getZones();
        if (! empty($this->selectedZone)) {
            $this->territories = $catalogs->getTerritoriesByZone($this->selectedZone);
        }
    }

    public function updatedSelectedZone(string $zone, CatalogsRepositoryInterface $catalogs): void
    {
        $this->selectedTerritory = '';
        $this->territories = ! empty($zone) ? $catalogs->getTerritoriesByZone($zone) : [];
        $this->resetPage();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedSeason(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyClosed(): void
    {
        $this->resetPage();
    }

    public bool $isCancelModalOpen = false;

    public string $cancellingPlanCode = '';

    public string $cancellingCustomerName = '';

    public string $cancellationComment = '';

    public function openCancelModal(string $planCode, string $customerName): void
    {
        $this->cancellingPlanCode = $planCode;
        $this->cancellingCustomerName = $customerName;
        $this->cancellationComment = '';
        $this->isCancelModalOpen = true;
    }

    public function closeCancelModal(): void
    {
        $this->isCancelModalOpen = false;
        $this->cancellingPlanCode = '';
        $this->cancellingCustomerName = '';
        $this->cancellationComment = '';
    }

    public function confirmCancelPlan(CancelPlanUseCase $useCase): void
    {
        $this->validate([
            'cancellationComment' => 'required|min:5|max:245',
        ], [
            'cancellationComment.required' => 'El comentario o motivo de cancelación es obligatorio.',
            'cancellationComment.min' => 'El comentario debe contener al menos 5 caracteres.',
        ]);

        $result = $useCase->execute($this->cancellingPlanCode, $this->cancellationComment);

        if (! empty($result['estado']) && $result['estado'] === 1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['mensaje'] ?? 'Error al cancelar el plan.',
            ]);

            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "El plan {$this->cancellingPlanCode} ha sido cerrado/cancelado correctamente.",
        ]);

        $this->closeCancelModal();
    }

    public function createVersion(string $planCode, VersionPlanUseCase $useCase): void
    {
        $result = $useCase->execute($planCode);
        if (! empty($result['estado']) && $result['estado'] === 1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['mensaje'] ?? 'Error al generar nueva versión.',
            ]);

            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Se ha creado una nueva versión para el plan {$planCode}.",
        ]);
    }

    public function render(GetCustomerPlansUseCase $getPlansUseCase)
    {
        $filter = new PlanFilterDTO(
            customerCode: $this->cardCode,
            zone: ! empty($this->selectedZone) ? $this->selectedZone : null,
            territory: ! empty($this->selectedTerritory) ? $this->selectedTerritory : null,
            season: $this->selectedSeason,
            onlyClosed: $this->onlyClosed,
            searchQuery: ! empty($this->searchQuery) ? $this->searchQuery : null,
        );

        $plansResult = $getPlansUseCase->execute($filter);

        return view('livewire.plans.plan-index', [
            'plans' => $plansResult['data'] ?? [],
            'total' => $plansResult['total'] ?? 0,
        ])->layout('layouts.app');
    }
}

<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Application\UseCases\Plans\CancelPlanUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Livewire\Component;

final class SeguimientoPage extends Component
{
    public string $texto = '';

    public string $zona = '';

    public int $page = 1;

    public int $limit = 10;

    public int $totalPages = 1;

    public array $zonas = [];

    public array $clientes = [];

    public ?string $expandedClient = null;

    public array $planesPorCliente = [];

    public ?string $loadingPlanes = null;

    public bool $isLoading = false;

    public string $errorMensaje = '';

    public function mount(CatalogsRepositoryInterface $catalogs): void
    {
        $this->zonas = $catalogs->getZones();
        $this->cargarClientes($catalogs);
    }

    public function updatedTexto(): void
    {
        $this->page = 1;
        $this->cargarClientes(app(CatalogsRepositoryInterface::class));
    }

    public function updatedZona(): void
    {
        $this->page = 1;
        $this->cargarClientes(app(CatalogsRepositoryInterface::class));
    }

    public function setPage(int $pageNumber): void
    {
        if ($pageNumber < 1 || $pageNumber > $this->totalPages) {
            return;
        }
        $this->page = $pageNumber;
        $this->cargarClientes(app(CatalogsRepositoryInterface::class));
    }

    public function cargarClientes(CatalogsRepositoryInterface $catalogs): void
    {
        $this->isLoading = true;
        $this->errorMensaje = '';

        try {
            $this->clientes = $catalogs->searchCustomers($this->texto, $this->zona ?: null, $this->page, $this->limit);
            $this->totalPages = max(1, (int) ceil(count($this->clientes) / $this->limit));
        } catch (\Throwable $e) {
            $this->errorMensaje = 'Error al cargar clientes: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function toggleCliente(string $cardCode, FertilizationPlanRepositoryInterface $planRepo): void
    {
        if ($this->expandedClient === $cardCode) {
            $this->expandedClient = null;

            return;
        }

        $this->expandedClient = $cardCode;

        if (isset($this->planesPorCliente[$cardCode])) {
            return;
        }

        $this->loadingPlanes = $cardCode;

        try {
            $filter = new PlanFilterDTO(customerCode: $cardCode);
            $result = $planRepo->getPlansByFilter($filter);
            $this->planesPorCliente[$cardCode] = $result['data'] ?? [];
        } catch (\Throwable $e) {
            $this->planesPorCliente[$cardCode] = [];
        } finally {
            $this->loadingPlanes = null;
        }
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

    public function confirmCancelPlan(
        CancelPlanUseCase $useCase,
        FertilizationPlanRepositoryInterface $planRepo
    ): void {
        $this->validate([
            'cancellationComment' => 'required|min:3|max:245',
        ], [
            'cancellationComment.required' => 'El comentario o motivo de cancelación es obligatorio.',
            'cancellationComment.min' => 'El comentario debe contener al menos 3 caracteres.',
            'cancellationComment.max' => 'El comentario no puede exceder 245 caracteres.',
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
            'message' => "El plan {$this->cancellingPlanCode} ha sido cerrado correctamente.",
        ]);

        if ($this->expandedClient) {
            $filter = new PlanFilterDTO(customerCode: $this->expandedClient);
            $freshPlans = $planRepo->getPlansByFilter($filter);
            $this->planesPorCliente[$this->expandedClient] = $freshPlans['data'] ?? [];
        }

        $this->closeCancelModal();
    }

    public function render()
    {
        return view('livewire.tracking.seguimiento-page')
            ->layout('layouts.app', ['title' => 'Seguimiento de Planes']);
    }
}

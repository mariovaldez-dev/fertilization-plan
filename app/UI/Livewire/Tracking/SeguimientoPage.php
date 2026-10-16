<?php

declare(strict_types=1);

namespace App\UI\Livewire\Tracking;

use App\Application\UseCases\Plans\CancelPlanUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use App\Domain\FertilizationPlan\Enums\PlanStatusEnum;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Illuminate\Support\Facades\Log;
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
        $startTime = microtime(true);

        Log::info('[SeguimientoPage] Searching customers', [
            'texto' => $this->texto,
            'zona' => $this->zona,
            'page' => $this->page,
            'limit' => $this->limit,
        ]);

        try {
            $result = $catalogs->searchCustomersPaginated($this->texto, null, $this->page, $this->limit);
            $this->clientes = $result['items'] ?? [];
            $this->totalPages = max(1, (int) ($result['totalPages'] ?? 1));

            Log::info('[SeguimientoPage] Customers search completed', [
                'count' => count($this->clientes),
                'total' => $result['total'] ?? null,
                'totalPages' => $this->totalPages,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            $this->errorMensaje = 'Error al cargar clientes: '.$e->getMessage();
            Log::error('[SeguimientoPage] Error loading customers', [
                'error' => $e->getMessage(),
                'texto' => $this->texto,
                'zona' => $this->zona,
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function toggleCliente(string $cardCode, FertilizationPlanRepositoryInterface $planRepo): void
    {
        if ($this->expandedClient === $cardCode) {
            Log::info('[SeguimientoPage] Collapsing client', ['cardCode' => $cardCode]);
            $this->expandedClient = null;

            return;
        }

        Log::info('[SeguimientoPage] Expanding client details', ['cardCode' => $cardCode]);
        $this->expandedClient = $cardCode;

        if (isset($this->planesPorCliente[$cardCode])) {
            Log::info('[SeguimientoPage] Using cached plans for client', [
                'cardCode' => $cardCode,
                'plans_count' => count($this->planesPorCliente[$cardCode]),
            ]);

            return;
        }

        $this->loadingPlanes = $cardCode;
        $startTime = microtime(true);

        try {
            $filter = new PlanFilterDTO(customerCode: $cardCode);
            $result = $planRepo->getPlansByFilter($filter);

            $this->planesPorCliente[$cardCode] = $this->formatPlans($result['data'] ?? []);

            Log::info('[SeguimientoPage] Client plans loaded from API', [
                'cardCode' => $cardCode,
                'plans_count' => count($this->planesPorCliente[$cardCode]),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            $this->planesPorCliente[$cardCode] = [];
            Log::error('[SeguimientoPage] Error loading client plans', [
                'cardCode' => $cardCode,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loadingPlanes = null;
        }
    }

    /**
     * @param  array<int, mixed>  $plans
     * @return array<int, array<string, mixed>>
     */
    private function formatPlans(array $plans): array
    {
        $plansArray = [];
        foreach ($plans as $p) {
            $statusVal = $p->status instanceof PlanStatusEnum
                ? $p->status->value
                : (string) ($p->status ?? 'Activo');
            $statusLabel = $p->status instanceof PlanStatusEnum
                ? $p->status->label()
                : (string) ($p->status ?? 'Activo');

            $plansArray[] = [
                'planCode' => (string) $p->planCode,
                'version' => (int) $p->version,
                'customerCode' => (string) $p->customerCode,
                'customerName' => (string) ($p->customerName ?: "Plan #{$p->planCode}"),
                'currentCropName' => (string) ($p->currentCropName ?? 'Sin cultivo'),
                'currentCropCode' => (string) ($p->currentCropCode ?? ''),
                'hectares' => (float) $p->hectares,
                'estimatedSowingDate' => (string) ($p->estimatedSowingDate ?? ''),
                'status' => $statusVal,
                'statusLabel' => $statusLabel,
                'isReadOnly' => method_exists($p, 'isReadOnly') ? $p->isReadOnly() : false,
            ];
        }

        return $plansArray;
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

        Log::info('[SeguimientoPage] Opened cancel modal', [
            'planCode' => $planCode,
            'customerName' => $customerName,
        ]);
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

        Log::info('[SeguimientoPage] Confirming plan cancellation', [
            'planCode' => $this->cancellingPlanCode,
            'comment' => $this->cancellationComment,
        ]);

        $result = $useCase->execute($this->cancellingPlanCode, $this->cancellationComment);

        if (! empty($result['estado']) && $result['estado'] === 1) {
            Log::warning('[SeguimientoPage] Plan cancellation rejected by API', [
                'planCode' => $this->cancellingPlanCode,
                'result' => $result,
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $result['mensaje'] ?? 'Error al cancelar el plan.',
            ]);

            return;
        }

        Log::info('[SeguimientoPage] Plan successfully cancelled', [
            'planCode' => $this->cancellingPlanCode,
            'message' => $result['mensaje'] ?? null,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "El plan {$this->cancellingPlanCode} ha sido cerrado correctamente.",
        ]);

        if ($this->expandedClient) {
            $filter = new PlanFilterDTO(customerCode: $this->expandedClient);
            $freshPlans = $planRepo->getPlansByFilter($filter);
            $this->planesPorCliente[$this->expandedClient] = $this->formatPlans($freshPlans['data'] ?? []);
        }

        $this->closeCancelModal();
    }

    public function render()
    {
        return view('livewire.tracking.seguimiento-page')
            ->layout('layouts.app', ['title' => 'Seguimiento de Planes']);
    }
}

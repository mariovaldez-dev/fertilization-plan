<?php

namespace Tests\Feature\Tracking;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use App\UI\Livewire\Results\CaptureResultsModal;
use App\UI\Livewire\Tracking\SeguimientoPage;
use App\UI\Livewire\Tracking\SeguimientoPlan;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SeguimientoTest extends TestCase
{
    private AuthenticatedUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $this->user]);
    }

    /** @test */
    public function seguimiento_page_can_be_rendered_and_searches_clients(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getZones')->andReturn([]);
        $catalogsMock->shouldReceive('searchCustomers')->with('', null, 1, 10)->andReturn([]);
        $catalogsMock->shouldReceive('searchCustomers')->with('Agricola', null, 1, 10)->andReturn([
            ['CardCode' => 'CLI-001', 'CardName' => 'Agricola del Valle', 'Cellular' => '6681234567'],
        ]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPage::class)
            ->assertStatus(200)
            ->set('texto', 'Agricola')
            ->assertCount('clientes', 1);
    }

    /** @test */
    public function seguimiento_plan_validates_stage_products_before_saving(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getProducts')->andReturn([]);
        $catalogsMock->shouldReceive('getApplicationMethods')->andReturn([
            ['Code' => 1, 'Name' => 'Fertirriego'],
        ]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-100')->andReturn(null);
        $planRepoMock->shouldReceive('getTrackingStages')->with('PF-100', Mockery::any())->andReturn([]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-100'])
            ->set('etapas', [])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'No hay etapas para guardar en el seguimiento.');
    }

    /** @test */
    public function capture_results_modal_recalculates_kpis(): void
    {
        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-100')->andReturn(null);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(CaptureResultsModal::class)
            ->set('rendimiento', 15.0)
            ->set('rendimientoTradicional', 10.0)
            ->set('precioCosecha', 5000.0)
            ->set('costoTotal', 35000.0)
            ->set('costoTradicional', 30000.0)
            ->assertSet('utilidadExtra', 25000.0)
            ->assertSet('inversionExtra', 5000.0)
            ->assertSet('retornoInversion', 5.0);
    }
}

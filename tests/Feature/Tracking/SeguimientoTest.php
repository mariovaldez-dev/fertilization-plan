<?php

namespace Tests\Feature\Tracking;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\Entities\FertilizationPlan;
use App\Domain\FertilizationPlan\Enums\PlanStatusEnum;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use App\UI\Livewire\Reports\ProposalPdfModal;
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
        $catalogsMock->shouldReceive('searchCustomersPaginated')->with('', null, 1, 10)->andReturn([
            'items' => [],
            'total' => 0,
            'totalPages' => 1,
            'page' => 1,
            'limit' => 10,
        ]);
        $catalogsMock->shouldReceive('searchCustomersPaginated')->with('Agricola', null, 1, 10)->andReturn([
            'items' => [
                ['CardCode' => 'CLI-001', 'CardName' => 'CLI-001 - Agricola del Valle', 'Cellular' => '6681234567'],
            ],
            'total' => 1,
            'totalPages' => 1,
            'page' => 1,
            'limit' => 10,
        ]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPage::class)
            ->assertStatus(200)
            ->set('texto', 'Agricola')
            ->assertCount('clientes', 1)
            ->assertSeeHtml('table-fixed')
            ->assertSeeHtml('Agricola del Valle')
            ->assertDontSeeHtml('CLI-001 - Agricola del Valle');
    }

    /** @test */
    public function seguimiento_page_can_expand_client_and_serialize_plans_without_type_error(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getZones')->andReturn([]);
        $catalogsMock->shouldReceive('searchCustomersPaginated')->andReturn([
            'items' => [
                ['CardCode' => 'C05709', 'CardName' => 'Plan Test', 'Cellular' => '6681234567'],
            ],
            'total' => 1,
            'totalPages' => 1,
            'page' => 1,
            'limit' => 10,
        ]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        $planEntity = new FertilizationPlan(
            planCode: '270',
            version: 1,
            customerCode: 'C05709',
            customerName: '270 - Plan Test - C05709',
            advisorId: '',
            advisorName: '',
            zone: '',
            territory: '',
            season: '',
            hectares: 12.0,
            soilType: '',
            currentCropCode: '1',
            currentCropName: 'Maíz',
            estimatedSowingDate: '2025-10-22T00:00:00',
            status: PlanStatusEnum::ACTIVE,
        );

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlansByFilter')
            ->andReturn([
                'estado' => 0,
                'data' => [$planEntity],
            ]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPage::class)
            ->call('toggleCliente', 'C05709')
            ->assertSet('expandedClient', 'C05709')
            ->assertSee('270 - Plan Test - C05709')
            ->assertSee('Maíz');
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
    public function seguimiento_plan_renders_with_header_and_supports_skeleton_loading(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getProducts')->andReturn([]);
        $catalogsMock->shouldReceive('getApplicationMethods')->andReturn([]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-999')->andReturn(null);
        $planRepoMock->shouldReceive('getTrackingStages')->with('PF-999', Mockery::any())->andReturn([]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-999'])
            ->assertStatus(200)
            ->assertSeeHtml('Nutrición Vegetal')
            ->set('isLoading', true)
            ->assertSeeHtml('BENTO HEADER SKELETON')
            ->set('isLoading', false)
            ->assertDontSeeHtml('BENTO HEADER SKELETON');
    }

    /** @test */
    public function seguimiento_plan_validates_all_product_and_stage_rules(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getProducts')->andReturn([
            ['itemCode' => 'P100', 'name' => 'Urea 46%'],
        ]);
        $catalogsMock->shouldReceive('getApplicationMethods')->andReturn([
            ['Code' => 1, 'Name' => 'Fertirriego'],
        ]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-101')->andReturn(null);
        $planRepoMock->shouldReceive('getTrackingStages')->with('PF-101', Mockery::any())->andReturn([]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        // 1. Falta fecha de aplicación
        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-101'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Desarrollo',
                    'fechaAplicacion' => '',
                    'idAplicacion' => '1',
                    'productos' => [
                        ['tipo' => 'impulsora', 'idProducto' => 'P100', 'dosis' => 10, 'precio' => 0],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'Falta la fecha de aplicación en la etapa: "Desarrollo".');

        // 2. Falta método de aplicación
        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-101'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Desarrollo',
                    'fechaAplicacion' => '2026-10-15',
                    'idAplicacion' => '',
                    'productos' => [
                        ['tipo' => 'impulsora', 'idProducto' => 'P100', 'dosis' => 10, 'precio' => 0],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'Falta seleccionar el método de aplicación en la etapa: "Desarrollo".');

        // 3. Falta seleccionar producto Impulsora
        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-101'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Desarrollo',
                    'fechaAplicacion' => '2026-10-15',
                    'idAplicacion' => '1',
                    'productos' => [
                        ['tipo' => 'impulsora', 'idProducto' => '', 'dosis' => 10, 'precio' => 0],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'Falta seleccionar un producto "Impulsora" en la etapa: "Desarrollo".');

        // 4. Falta nombre en producto Otros
        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-101'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Desarrollo',
                    'fechaAplicacion' => '2026-10-15',
                    'idAplicacion' => '1',
                    'productos' => [
                        ['tipo' => 'otros', 'nombreProducto' => '', 'dosis' => 10, 'precio' => 50],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'Falta capturar el nombre del producto "Otros" en la etapa: "Desarrollo".');

        // 5. Dosis menor o igual a cero
        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-101'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Desarrollo',
                    'fechaAplicacion' => '2026-10-15',
                    'idAplicacion' => '1',
                    'productos' => [
                        ['tipo' => 'impulsora', 'idProducto' => 'P100', 'dosis' => 0, 'precio' => 0],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('errorMessage', 'La dosis para el producto "P100" en la etapa: "Desarrollo" debe ser mayor a cero.');
    }

    /** @test */
    public function seguimiento_plan_saves_successfully(): void
    {
        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getProducts')->andReturn([]);
        $catalogsMock->shouldReceive('getApplicationMethods')->andReturn([]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-555')->andReturn(null);
        $planRepoMock->shouldReceive('getTrackingStages')->with('PF-555', Mockery::any())->andReturn([]);
        $planRepoMock->shouldReceive('saveTracking')->once()->andReturn([
            'success' => true,
            'message' => 'Seguimiento guardado correctamente.',
        ]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(SeguimientoPlan::class, ['planCode' => 'PF-555'])
            ->set('etapas', [
                [
                    'codeEtapa' => 1,
                    'nombreEtapa' => 'Siembra',
                    'fechaAplicacion' => '2026-10-15',
                    'idAplicacion' => '1',
                    'comentario' => 'Aplicación realizada con éxito',
                    'productos' => [
                        [
                            'tipo' => 'impulsora',
                            'idProducto' => 'P100',
                            'nombreProducto' => 'Urea',
                            'dosis' => 150.0,
                            'precio' => 0.0,
                            'Proveedor' => 'GRUPO IMPULSORA',
                        ],
                    ],
                ],
            ])
            ->call('guardarSeguimiento')
            ->assertSet('showSuccessModal', true)
            ->assertDispatched('notify');
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

    /** @test */
    public function capture_results_modal_shows_skeleton_while_loading(): void
    {
        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-300')->andReturn(null);
        $planRepoMock->shouldReceive('getResultsPdfData')->with('PF-300')->andReturn([]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(CaptureResultsModal::class)
            ->call('open', 'PF-300')
            ->assertSet('isOpen', true)
            ->assertSet('isLoading', true)
            ->assertSee('animate-pulse')
            ->call('loadResultsData')
            ->assertSet('isLoading', false)
            ->assertSee('Dictamen Final de Cosecha')
            ->assertSee('1. Datos de Cosecha Actual');
    }

    /** @test */
    public function proposal_pdf_modal_opens_without_iframe_preview_and_supports_download(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProposalPdfModal::class)
            ->call('open', '270', 1, 'Maite Armenta', '6681234567')
            ->assertSet('isOpen', true)
            ->assertSet('planCode', '270')
            ->assertSet('recipientPhone', '6681234567')
            ->assertDontSee('<iframe', false)
            ->assertSee('Acciones del Plan 270')
            ->assertSee('Compartir')
            ->assertSee('Descargar');
    }

    /** @test */
    public function proposal_pdf_modal_shows_warning_and_disables_sharing_when_phone_is_missing(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProposalPdfModal::class)
            ->call('open', '270', 1, 'Maite Armenta', '')
            ->assertSet('isOpen', true)
            ->assertSet('isPhoneMissing', true)
            ->assertSee('Cliente sin celular registrado');
    }

    /** @test */
    public function capture_results_modal_saves_results_and_generates_pdf(): void
    {
        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-200')->andReturn(null);
        $planRepoMock->shouldReceive('getResultsPdfData')->with('PF-200')->andReturn([
            'planInfo' => [
                'PlanCode' => 'PF-200',
                'CardName' => 'Agricola Test',
                'Parcela' => 'Lote 1',
                'Superficie' => 10,
                'CultivoActual' => 'Maíz',
                'CostoPorHa' => 32000,
                'CostoTradicional' => 28000,
                'RendimientoFinal' => 14.2,
                'RendimientoTestigo' => 10.5,
            ],
            'seguimiento' => [],
            'nutricional' => [],
        ]);
        $planRepoMock->shouldReceive('captureResults')->once()->andReturn([
            'success' => true,
            'message' => 'Resultados guardados.',
        ]);
        $planRepoMock->shouldReceive('saveResultsPdf')->once()->with('PF-200', 1, 'Resultados', Mockery::any())->andReturn([
            'success' => true,
        ]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(CaptureResultsModal::class)
            ->set('planCode', 'PF-200')
            ->set('fechaCosecha', '2026-10-15')
            ->set('rendimiento', 14.2)
            ->set('rendimientoTradicional', 10.5)
            ->set('dosis', 1.0)
            ->set('precioCosecha', 5500.0)
            ->set('costoTotal', 32000.0)
            ->set('costoTradicional', 28000.0)
            ->set('recomendaciones', 'Monitoreo constante.')
            ->set('propuesta', 'Nutrición balanceada.')
            ->set('buenasPracticas', 'Riego controlado.')
            ->set('coordenadas', '24.808000,-107.394000')
            ->call('saveResults')
            ->assertSet('isSuccess', true)
            ->assertDispatched('plan-results-updated');
    }

    /** @test */
    public function capture_results_modal_updates_and_saves_coordinates(): void
    {
        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanByCode')->with('PF-999')->andReturn(null);
        $planRepoMock->shouldReceive('getResultsPdfData')->with('PF-999')->andReturn([]);
        $planRepoMock->shouldReceive('captureResults')->once()->withArgs(function ($dto) {
            return $dto->coordinates === '24.808000,-107.394000';
        })->andReturn([
            'success' => true,
            'message' => 'Resultados guardados.',
        ]);
        $planRepoMock->shouldReceive('saveResultsPdf')->once()->andReturn(['success' => true]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($this->user)
            ->test(CaptureResultsModal::class)
            ->set('planCode', 'PF-999')
            ->set('fechaCosecha', '2026-10-15')
            ->set('rendimiento', 12.0)
            ->set('rendimientoTradicional', 10.0)
            ->set('dosis', 1.0)
            ->set('precioCosecha', 5000.0)
            ->set('costoTotal', 30000.0)
            ->set('costoTradicional', 25000.0)
            ->set('recomendaciones', 'Recomendaciones')
            ->set('propuesta', 'Propuesta')
            ->set('buenasPracticas', 'BPA')
            ->call('setCoordinates', 24.808, -107.394)
            ->assertSet('coordenadas', '24.808000,-107.394000')
            ->call('saveResults')
            ->assertSet('isSuccess', true);
    }
}

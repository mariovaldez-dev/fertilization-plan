<?php

namespace Tests\Feature\Wizard;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Domain\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use App\UI\Livewire\Wizard\PlanWizard;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PlanWizardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/plan-fertilizacion/guardar-pdf' => Http::response(['success' => true, 'mensaje' => 'PDF guardado'], 200),
            '*/asesores/buscar*' => Http::response(['data' => []], 200),
        ]);

        $catalogsMock = Mockery::mock(CatalogsRepositoryInterface::class);
        $catalogsMock->shouldReceive('getZones')->andReturn([]);
        $catalogsMock->shouldReceive('getSoilTypes')->andReturn([]);
        $catalogsMock->shouldReceive('getCropTypes')->andReturn([]);
        $catalogsMock->shouldReceive('getProducts')->andReturn([]);
        $catalogsMock->shouldReceive('getApplicationMethods')->andReturn([]);
        $this->app->instance(CatalogsRepositoryInterface::class, $catalogsMock);
    }

    /** @test */
    public function step_1_requires_coordinates_to_advance_to_step_2(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('customerCode', 'C001')
            ->set('advisorId', '100')
            ->set('parcela', 'Lote 1')
            ->set('hectares', 15.0)
            ->set('soilType', 'Franco')
            ->set('analisisSuelo', 'NO')
            ->set('latitude', null)
            ->set('longitude', null)
            ->call('nextStep')
            ->assertHasErrors(['latitude', 'longitude'])
            ->assertSet('currentStep', 1);
    }

    /** @test */
    public function setting_coordinates_clears_error_and_allows_advancing(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('customerCode', 'C001')
            ->set('advisorId', '100')
            ->set('parcela', 'Lote 1')
            ->set('hectares', 15.0)
            ->set('soilType', 'Franco')
            ->set('analisisSuelo', 'NO')
            ->call('setCoordinates', 24.8080, -107.3940)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 2);
    }

    /** @test */
    public function it_downloads_proposal_pdf_stream(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        $component = Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('savedPlanCode', 'PF-999')
            ->set('customerName', 'Agricola Test')
            ->call('downloadProposalPdf');

        $component->assertFileDownloaded('PlanFertilizacion-PF-999-V1.pdf');
    }

    /** @test */
    public function it_shares_proposal_pdf_via_whatsapp(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        $whatsAppMock = Mockery::mock(WhatsAppServiceInterface::class);
        $whatsAppMock->shouldReceive('sendDocument')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => '¡El plan fue enviado exitosamente por WhatsApp!',
            ]);
        $this->app->instance(WhatsAppServiceInterface::class, $whatsAppMock);

        Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('savedPlanCode', 'PF-999')
            ->set('customerName', 'Agricola Test')
            ->set('customerPhone', '6681234567')
            ->set('advisorName', 'Ing. Carlos')
            ->set('advisorPhone', '6671234567')
            ->call('shareViaWhatsApp')
            ->assertSet('whatsAppSuccess', '¡El plan fue enviado exitosamente por WhatsApp!')
            ->assertSet('whatsAppError', null);
    }

    /** @test */
    public function it_blocks_whatsapp_share_when_customer_phone_is_missing(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('showSuccessModal', true)
            ->set('savedPlanCode', 'PF-999')
            ->set('customerName', 'Agricola Test')
            ->set('customerPhone', '')
            ->call('shareViaWhatsApp')
            ->assertSet('isCustomerPhoneMissing', true)
            ->assertSet('whatsAppError', 'No se puede compartir: El cliente no cuenta con número celular registrado en SAP.')
            ->assertSee('Cliente sin celular registrado');
    }

    /** @test */
    public function it_shares_proposal_pdf_via_whatsapp_to_customer_phone(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        $whatsAppMock = Mockery::mock(WhatsAppServiceInterface::class);
        $whatsAppMock->shouldReceive('sendDocument')
            ->once()
            ->with(Mockery::on(function ($dto) {
                return $dto->recipientPhone === '6689876543';
            }))
            ->andReturn([
                'success' => true,
                'message' => '¡El plan fue enviado exitosamente por WhatsApp!',
            ]);
        $this->app->instance(WhatsAppServiceInterface::class, $whatsAppMock);

        Livewire::actingAs($user)
            ->test(PlanWizard::class)
            ->set('savedPlanCode', 'PF-888')
            ->set('customerName', 'Agricola del Valle')
            ->set('customerPhone', '6689876543')
            ->set('advisorPhone', '6671234567')
            ->call('shareViaWhatsApp')
            ->assertSet('whatsAppSuccess', '¡El plan fue enviado exitosamente por WhatsApp!')
            ->assertSet('whatsAppError', null);
    }

    /** @test */
    public function it_loads_plan_for_versioning_and_lands_on_step_3_with_bento_card(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanForVersioning')
            ->with('PF-270')
            ->andReturn([
                'maestro' => [
                    'CardCode' => 'CLI-01',
                    'CardName' => 'Agricola El Alamo',
                    'Parcela' => 'Parcela Norte',
                    'Superficie' => 45.0,
                    'CodeTipoSuelo' => 'Arcilloso',
                    'AnalisisSuelo' => 'SI',
                    'Coordenadas' => '25.7904,-108.9950',
                    'CodeCultAnterior' => 'MAIZ',
                    'RendimientoReal' => 12.0,
                    'RendimientoMax' => 14.5,
                    'CodeCultivo' => 'TRIGO',
                    'nombrecultivo' => 'Trigo Cristalino',
                    'FechaSiembra' => '2026-11-15T00:00:00',
                    'VariedadSemilla' => 'Cirno C2008',
                    'ObjetivoProd' => 8.5,
                    'IDAsesorAgr' => '100',
                    'U_Asesor' => 'Ing. Juan Perez',
                    'Cellular' => '6681234567',
                    'VersionActual' => '1',
                ],
                'etapas' => [
                    [
                        'codeEtapa' => 1,
                        'nameEtapa' => 'Pre-Siembra',
                        'listaProductos' => json_encode([
                            ['id' => 'UREA-01', 'nombre' => 'Urea Granulada 46%', 'dosis' => 150.0],
                        ]),
                        'idAplicacion' => '1',
                        'fechaAplicacion' => '2026-11-10T00:00:00',
                        'comentario' => 'Aplicar antes del riego de asiento',
                    ],
                ],
            ]);
        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($user)
            ->test(PlanWizard::class, ['planCode' => 'PF-270'])
            ->call('loadInitialData')
            ->assertSet('isVersioning', true)
            ->assertSet('basePlanCode', 'PF-270')
            ->assertSet('baseVersion', 1)
            ->assertSet('newVersion', 2)
            ->assertSet('currentStep', 3)
            ->assertSet('customerCode', 'CLI-01')
            ->assertSet('customerName', 'Agricola El Alamo')
            ->assertSet('parcela', 'Parcela Norte')
            ->assertSet('hectares', 45.0)
            ->assertSet('soilType', 'Arcilloso')
            ->assertSet('analisisSuelo', 'SI')
            ->assertSee('Versionar Plan (Base: V1)')
            ->assertSee('Nueva Versión: V2')
            ->assertSee('Regresar al Seguimiento')
            ->assertSee('Guardar Versión');
    }

    /** @test */
    public function it_successfully_saves_new_version_and_dispatches_api(): void
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Advisor',
            'email' => 'advisor@grupoimpulsora.com',
            'rol' => '2',
            'sessionId' => 'test-session',
        ]);
        session(['authenticated_user' => $user]);

        $planRepoMock = Mockery::mock(FertilizationPlanRepositoryInterface::class);
        $planRepoMock->shouldReceive('getPlanForVersioning')
            ->with('PF-270')
            ->andReturn([
                'maestro' => [
                    'CardCode' => 'CLI-01',
                    'CardName' => 'Agricola El Alamo',
                    'Parcela' => 'Parcela Norte',
                    'Superficie' => 45.0,
                    'CodeTipoSuelo' => 'Arcilloso',
                    'AnalisisSuelo' => 'SI',
                    'Coordenadas' => '25.7904,-108.9950',
                    'CodeCultAnterior' => 'MAIZ',
                    'RendimientoReal' => 12.0,
                    'RendimientoMax' => 14.5,
                    'CodeCultivo' => 'TRIGO',
                    'nombrecultivo' => 'Trigo Cristalino',
                    'FechaSiembra' => '2026-11-15T00:00:00',
                    'VariedadSemilla' => 'Cirno C2008',
                    'ObjetivoProd' => 8.5,
                    'IDAsesorAgr' => '100',
                    'U_Asesor' => 'Ing. Juan Perez',
                    'Cellular' => '6681234567',
                    'VersionActual' => '1',
                ],
                'etapas' => [
                    [
                        'codeEtapa' => 1,
                        'nameEtapa' => 'Pre-Siembra',
                        'listaProductos' => json_encode([
                            ['id' => 'UREA-01', 'nombre' => 'Urea Granulada 46%', 'dosis' => 150.0],
                        ]),
                        'idAplicacion' => '1',
                        'fechaAplicacion' => '2026-11-10T00:00:00',
                        'comentario' => 'Aplicar antes del riego de asiento',
                    ],
                ],
            ]);

        $planRepoMock->shouldReceive('versionPlanFull')
            ->once()
            ->with(
                'PF-270',
                Mockery::on(function ($maestro) {
                    return $maestro['Code'] === 'PF-270'
                        && $maestro['CardCode'] === 'CLI-01'
                        && $maestro['Superficie'] === 45.0;
                }),
                Mockery::on(function ($etapas) {
                    return count($etapas) === 1 && $etapas[0]['codeEtapa'] === 1;
                })
            )
            ->andReturn([
                'success' => true,
                'version' => 2,
                'message' => 'Versión guardada',
            ]);

        $planRepoMock->shouldReceive('savePlanPdf')
            ->once()
            ->with('PF-270', 2, 'Activo', Mockery::any())
            ->andReturn(['success' => true]);

        $this->app->instance(FertilizationPlanRepositoryInterface::class, $planRepoMock);

        Livewire::actingAs($user)
            ->test(PlanWizard::class, ['planCode' => 'PF-270'])
            ->call('loadInitialData')
            ->call('savePlan')
            ->assertSet('showSuccessModal', true)
            ->assertSet('savedPlanCode', 'PF-270')
            ->assertSet('newVersion', 2)
            ->assertSee('¡Versión V2 creada exitosamente!')
            ->assertSee('Plan Versionado:');
    }
}

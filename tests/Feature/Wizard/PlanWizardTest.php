<?php

namespace Tests\Feature\Wizard;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use App\UI\Livewire\Wizard\PlanWizard;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PlanWizardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            ->set('advisorName', 'Ing. Carlos')
            ->set('advisorPhone', '6671234567')
            ->call('shareViaWhatsApp')
            ->assertSet('whatsAppSuccess', '¡El plan fue enviado exitosamente por WhatsApp!')
            ->assertSet('whatsAppError', null);
    }
}

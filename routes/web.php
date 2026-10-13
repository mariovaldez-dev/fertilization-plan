<?php

use App\UI\Livewire\Auth\LoginForm;
use App\UI\Livewire\Dashboard\DashboardPage;
use App\UI\Livewire\Plans\PlanIndex;
use App\UI\Livewire\Tracking\SeguimientoPage;
use App\UI\Livewire\Tracking\SeguimientoPlan;
use App\UI\Livewire\Wizard\PlanWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autenticación
Route::get('/login', LoginForm::class)->name('login')->middleware('guest');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// Rutas protegidas
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');

    // Generar y Versionar Plan de Fertilización
    Route::get('/plan', PlanWizard::class)->name('plan.create');
    Route::get('/plan/versionar/{planCode}', PlanWizard::class)->name('plan.version');
    Route::get('/wizard', PlanWizard::class)->name('wizard.index');

    // Seguimiento y Búsqueda de Planes
    Route::get('/seguimiento', SeguimientoPage::class)->name('seguimiento.index');
    Route::get('/seguimiento/{cardCode}/planes', PlanIndex::class)->name('seguimiento.planes');
    Route::get('/plans', PlanIndex::class)->name('plans.index');

    // Edición de Seguimiento por Plan
    Route::get('/seguimiento-plan/{planCode?}', SeguimientoPlan::class)->name('seguimiento.plan');
    Route::get('/plans/{planCode}/tracking', SeguimientoPlan::class)->name('tracking.edit');
});

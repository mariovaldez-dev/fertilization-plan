<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Auth\Repositories\IAuthRepository;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Domain\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Infrastructure\Auth\AuthRepository;
use App\Infrastructure\ExternalServices\UltraMsgWhatsAppService;
use App\Infrastructure\Repositories\HttpCatalogsRepository;
use App\Infrastructure\Repositories\HttpFertilizationPlanRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IAuthRepository::class, AuthRepository::class);
        $this->app->bind(FertilizationPlanRepositoryInterface::class, HttpFertilizationPlanRepository::class);
        $this->app->bind(CatalogsRepositoryInterface::class, HttpCatalogsRepository::class);
        $this->app->bind(WhatsAppServiceInterface::class, UltraMsgWhatsAppService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

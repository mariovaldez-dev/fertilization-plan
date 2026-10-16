<?php

declare(strict_types=1);

namespace App\Domain\Catalogs\Repositories;

interface CatalogsRepositoryInterface
{
    /**
     * @return array<int, array{CardCode: string, CardName: string, Zone: ?string, Cellular: string}>
     */
    public function searchCustomers(string $query, ?string $zone = null, int $page = 1, int $limit = 10): array;

    /**
     * @return array{items: array<int, array{CardCode: string, CardName: string, Zone: ?string, Cellular: string}>, total: int, totalPages: int, page: int, limit: int}
     */
    public function searchCustomersPaginated(string $query, ?string $zone = null, int $page = 1, int $limit = 10): array;

    /**
     * @return array<int, array{AdvisorId: string, AdvisorName: string}>
     */
    public function searchAdvisors(string $query): array;

    /**
     * @return array<int, array{Code: string, Name: string}>
     */
    public function getZones(): array;

    /**
     * @return array<int, array{Code: string, Name: string, ZoneCode: string}>
     */
    public function getTerritoriesByZone(string $zoneCode): array;

    /**
     * @return array<int, array{Code: string, Name: string}>
     */
    public function getCropTypes(): array;

    /**
     * @return array<int, array{cultivoId: string, nombreCultivo: string, fechaSiembra: ?string}>
     */
    public function getCustomerCrops(string $customerCode): array;

    /**
     * @return array<int, array{Code: string, Name: string}>
     */
    public function getSoilTypes(): array;

    /**
     * @return array<int, array{ItemCode: string, ItemName: string, U_Det_IngActivo: ?string}>
     */
    public function getProducts(): array;

    /**
     * @return array<int, array{Code: string, Name: string}>
     */
    public function getApplicationMethods(): array;

    /**
     * @return array{etapas: array}
     */
    public function getCropRecommendation(string $cropId, ?string $territory = null): array;
}

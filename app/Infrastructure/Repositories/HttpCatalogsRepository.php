<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class HttpCatalogsRepository implements CatalogsRepositoryInterface
{
    private const JSON_KEY = 'JSON_F52E2B61-18A1-11d1-B105-00805F49916B';

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.api_fert.url', 'http://localhost:8000/api'), '/');
    }

    private function client()
    {
        $token = session('auth_token');
        $http = Http::timeout(20)->baseUrl($this->baseUrl)->acceptJson();

        if (! empty($token)) {
            $http = $http->withToken($token);
        }

        return $http;
    }

    public function searchCustomers(string $query, ?string $zone = null, int $page = 1, int $limit = 10): array
    {
        try {
            $trimmed = trim($query);
            if (empty($trimmed)) {
                return [];
            }

            if (empty($zone)) {
                $resp2 = $this->client()->get('/clientes/buscar2', ['texto' => $trimmed]);
                if ($resp2->successful()) {
                    $data = $resp2->json();
                    $items = $data['data'] ?? $data['clientes'] ?? (is_array($data) ? $data : []);
                    if (is_array($items) && ! empty($items)) {
                        return array_map(function ($item) {
                            return [
                                'CardCode' => (string) ($item['CardCode'] ?? $item['codigo'] ?? $item['id'] ?? $item['code'] ?? ''),
                                'CardName' => (string) ($item['CardName'] ?? $item['nombre'] ?? $item['name'] ?? ''),
                                'Zone' => isset($item['Zone']) ? (string) $item['Zone'] : ($item['zona'] ?? $item['U_Territorio'] ?? null),
                            ];
                        }, $items);
                    }
                }
            }

            $response = $this->client()->get('/clientes/buscar', [
                'texto' => $trimmed,
                'zona' => $zone,
                'page' => $page,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['clientes'] ?? (is_array($data) ? $data : []);

                return array_map(function ($item) {
                    return [
                        'CardCode' => (string) ($item['CardCode'] ?? $item['codigo'] ?? $item['id'] ?? $item['code'] ?? ''),
                        'CardName' => (string) ($item['CardName'] ?? $item['nombre'] ?? $item['name'] ?? ''),
                        'Zone' => isset($item['Zone']) ? (string) $item['Zone'] : ($item['zona'] ?? $item['U_Territorio'] ?? null),
                    ];
                }, is_array($items) ? $items : []);
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] searchCustomers error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function searchAdvisors(string $query): array
    {
        try {
            $trimmed = trim($query);
            if (empty($trimmed)) {
                return [];
            }

            $response = $this->client()->get('/asesores/buscar', [
                'nombre' => $trimmed,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['asesores'] ?? (is_array($data) ? $data : []);

                return array_map(function ($item) {
                    return [
                        'AdvisorId' => (string) ($item['SlpCode'] ?? $item['AdvisorId'] ?? $item['Code'] ?? $item['codigo'] ?? $item['id'] ?? ''),
                        'AdvisorName' => (string) ($item['SlpName'] ?? $item['AdvisorName'] ?? $item['Name'] ?? $item['nombre'] ?? ''),
                        'Email' => (string) ($item['U_correo'] ?? $item['email'] ?? $item['correo'] ?? ''),
                        'Phone' => (string) ($item['U_Telefono'] ?? $item['Cellular'] ?? $item['celular'] ?? $item['telefono'] ?? ''),
                    ];
                }, is_array($items) ? $items : []);
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] searchAdvisors error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getZones(): array
    {
        try {
            $response = $this->client()->get('/zonas');

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['zonas'] ?? (is_array($data) ? $data : []);

                $mapped = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return ['Code' => $item, 'Name' => $item];
                    }
                    $code = (string) ($item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $item['Id'] ?? '');
                    $name = (string) ($item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['Descripcion'] ?? $item['descripcion'] ?? $code);

                    return ! empty($code) || ! empty($name) ? ['Code' => $code ?: $name, 'Name' => $name ?: $code] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [
                ['Code' => 'NTE', 'Name' => 'Norte'],
                ['Code' => 'SUR', 'Name' => 'Sur'],
                ['Code' => 'CEN', 'Name' => 'Centro'],
                ['Code' => 'BAJ', 'Name' => 'Bajío'],
            ];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getZones error', ['error' => $e->getMessage()]);

            return [
                ['Code' => 'NTE', 'Name' => 'Norte'],
                ['Code' => 'SUR', 'Name' => 'Sur'],
                ['Code' => 'CEN', 'Name' => 'Centro'],
                ['Code' => 'BAJ', 'Name' => 'Bajío'],
            ];
        }
    }

    public function getTerritoriesByZone(string $zoneCode): array
    {
        try {
            $response = $this->client()->get('/combos/buscar', ['opcion' => '2']);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['territorios'] ?? (is_array($data) ? $data : []);

                $mapped = array_values(array_filter(array_map(function ($item) use ($zoneCode) {
                    if (is_string($item)) {
                        return ['Code' => $item, 'Name' => $item, 'ZoneCode' => $zoneCode];
                    }
                    $code = (string) ($item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $item['Id'] ?? '');
                    $name = (string) ($item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $code);

                    return ! empty($code) || ! empty($name) ? ['Code' => $code ?: $name, 'Name' => $name ?: $code, 'ZoneCode' => $zoneCode] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getTerritoriesByZone error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getCropTypes(): array
    {
        try {
            $response = $this->client()->get('/combos/buscar', ['opcion' => '2']);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['cultivos'] ?? (is_array($data) ? $data : []);

                $mapped = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return ['Code' => $item, 'Name' => $item];
                    }
                    $name = (string) ($item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['Descripcion'] ?? $item['descripcion'] ?? $item['Code'] ?? $item['code'] ?? '');
                    $code = (string) ($item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $item['Id'] ?? $name);

                    return ! empty($name) || ! empty($code) ? ['Code' => $code ?: $name, 'Name' => $name ?: $code] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [
                ['Code' => 'MAIZ', 'Name' => 'Maíz'],
                ['Code' => 'TRIGO', 'Name' => 'Trigo'],
                ['Code' => 'SORGO', 'Name' => 'Sorgo'],
                ['Code' => 'TOMATE', 'Name' => 'Tomate'],
                ['Code' => 'PIMIENTO', 'Name' => 'Pimiento'],
                ['Code' => 'AGUACATE', 'Name' => 'Aguacate'],
                ['Code' => 'BERRIES', 'Name' => 'Berries'],
            ];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getCropTypes error', ['error' => $e->getMessage()]);

            return [
                ['Code' => 'MAIZ', 'Name' => 'Maíz'],
                ['Code' => 'TRIGO', 'Name' => 'Trigo'],
                ['Code' => 'SORGO', 'Name' => 'Sorgo'],
                ['Code' => 'TOMATE', 'Name' => 'Tomate'],
                ['Code' => 'PIMIENTO', 'Name' => 'Pimiento'],
                ['Code' => 'AGUACATE', 'Name' => 'Aguacate'],
                ['Code' => 'BERRIES', 'Name' => 'Berries'],
            ];
        }
    }

    public function getCustomerCrops(string $customerCode): array
    {
        try {
            $response = $this->client()->get('/clientes/cultivos', ['cardCode' => $customerCode]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['cultivos'] ?? (is_array($data) ? $data : []);

                return array_values(array_filter(array_map(function ($item) {
                    $nombreCultivo = (string) ($item['nombreCultivo'] ?? $item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['cultivoId'] ?? $item['code'] ?? '');
                    $cultivoId = (string) ($item['cultivoId'] ?? $item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['id'] ?? $nombreCultivo);
                    $fechaSiembra = isset($item['fechaSiembra']) ? (string) explode('T', (string) $item['fechaSiembra'])[0] : null;

                    return ! empty($nombreCultivo) || ! empty($cultivoId) ? [
                        'cultivoId' => $cultivoId ?: $nombreCultivo,
                        'nombreCultivo' => $nombreCultivo ?: $cultivoId,
                        'fechaSiembra' => $fechaSiembra,
                    ] : null;
                }, is_array($items) ? $items : [])));
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getCustomerCrops error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getSoilTypes(): array
    {
        try {
            $response = $this->client()->get('/combos/buscar', ['opcion' => '1']);

            if ($response->successful()) {
                $raw = $response->json();
                $items = $raw;

                if (is_array($raw) && count($raw) > 0 && isset($raw[0][self::JSON_KEY])) {
                    $items = json_decode((string) $raw[0][self::JSON_KEY], true) ?? [];
                } elseif (isset($raw['data']) || isset($raw['suelos'])) {
                    $items = $raw['data'] ?? $raw['suelos'];
                }

                $mapped = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return ['Code' => $item, 'Name' => $item];
                    }
                    $name = (string) ($item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['Descripcion'] ?? $item['descripcion'] ?? $item['Valor'] ?? '');
                    $code = (string) ($item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $item['Id'] ?? $name);

                    return ! empty($name) || ! empty($code) ? ['Code' => $code ?: $name, 'Name' => $name ?: $code] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [
                ['Code' => '1', 'Name' => 'Franco'],
                ['Code' => '2', 'Name' => 'Limoso'],
                ['Code' => '3', 'Name' => 'Arcilloso'],
                ['Code' => '4', 'Name' => 'Arenoso'],
            ];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getSoilTypes error', ['error' => $e->getMessage()]);

            return [
                ['Code' => '1', 'Name' => 'Franco'],
                ['Code' => '2', 'Name' => 'Limoso'],
                ['Code' => '3', 'Name' => 'Arcilloso'],
                ['Code' => '4', 'Name' => 'Arenoso'],
            ];
        }
    }

    public function getProducts(): array
    {
        try {
            $response = $this->client()->get('/combos/buscar', ['opcion' => '3']);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['productos'] ?? (is_array($data) ? $data : []);

                $mapped = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return ['ItemCode' => $item, 'ItemName' => $item, 'U_Det_IngActivo' => null];
                    }
                    $name = (string) ($item['ItemName'] ?? $item['itemName'] ?? $item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['Descripcion'] ?? $item['descripcion'] ?? $item['ItemCode'] ?? $item['itemCode'] ?? '');
                    $code = (string) ($item['ItemCode'] ?? $item['itemCode'] ?? $item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $name);

                    return ! empty($name) || ! empty($code) ? [
                        'ItemCode' => $code ?: $name,
                        'ItemName' => $name ?: $code,
                        'U_Det_IngActivo' => isset($item['U_Det_IngActivo']) ? (string) $item['U_Det_IngActivo'] : null,
                    ] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getProducts error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getApplicationMethods(): array
    {
        try {
            $response = $this->client()->get('/combos/buscar', ['opcion' => '4']);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? $data['metodos'] ?? (is_array($data) ? $data : []);

                $mapped = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return ['Code' => $item, 'Name' => $item];
                    }
                    $code = (string) ($item['Code'] ?? $item['code'] ?? $item['codigo'] ?? $item['Codigo'] ?? $item['id'] ?? $item['Id'] ?? $item['Valor'] ?? '');
                    $name = (string) ($item['Name'] ?? $item['name'] ?? $item['nombre'] ?? $item['Nombre'] ?? $item['Descripcion'] ?? $item['descripcion'] ?? $code);

                    return ! empty($code) || ! empty($name) ? ['Code' => $code ?: $name, 'Name' => $name ?: $code] : null;
                }, is_array($items) ? $items : [])));

                if (! empty($mapped)) {
                    return $mapped;
                }
            }

            return [
                ['Code' => 'Fertirriego', 'Name' => 'Fertirriego'],
                ['Code' => 'Foliar', 'Name' => 'Foliar'],
                ['Code' => 'Al Suelo / Granulado', 'Name' => 'Al Suelo / Granulado'],
                ['Code' => 'Drench', 'Name' => 'Drench'],
            ];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getApplicationMethods error', ['error' => $e->getMessage()]);

            return [
                ['Code' => 'Fertirriego', 'Name' => 'Fertirriego'],
                ['Code' => 'Foliar', 'Name' => 'Foliar'],
                ['Code' => 'Al Suelo / Granulado', 'Name' => 'Al Suelo / Granulado'],
                ['Code' => 'Drench', 'Name' => 'Drench'],
            ];
        }
    }

    public function getCropRecommendation(string $cropId, ?string $territory = null): array
    {
        try {
            $response = $this->client()->get('/plan-fertilizacion/recomendacion', [
                'cultivoId' => $cropId,
                'territorio' => $territory,
            ]);

            if (! $response->successful()) {
                $response = $this->client()->get('/cultivos/recomendacion', [
                    'cultivoId' => $cropId,
                    'territorio' => $territory,
                ]);
            }

            if ($response->successful()) {
                $data = $response->json();

                return $data['data'] ?? $data ?? ['etapas' => []];
            }

            return ['etapas' => []];
        } catch (\Throwable $e) {
            Log::error('[HttpCatalogsRepository] getCropRecommendation error', ['error' => $e->getMessage()]);

            return ['etapas' => []];
        }
    }
}

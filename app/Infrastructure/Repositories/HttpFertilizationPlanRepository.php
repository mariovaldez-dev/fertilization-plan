<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\CaptureResultDTO;
use App\Domain\FertilizationPlan\DTOs\CreatePlanDTO;
use App\Domain\FertilizationPlan\DTOs\PlanFilterDTO;
use App\Domain\FertilizationPlan\DTOs\UpdateTrackingDTO;
use App\Domain\FertilizationPlan\Entities\FertilizationPlan;
use App\Domain\FertilizationPlan\Entities\FertilizationProduct;
use App\Domain\FertilizationPlan\Entities\PhenologicalStage;
use App\Domain\FertilizationPlan\Enums\PlanStatusEnum;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class HttpFertilizationPlanRepository implements FertilizationPlanRepositoryInterface
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
        $http = Http::timeout(30)->baseUrl($this->baseUrl)->acceptJson();

        if (! empty($token)) {
            $http = $http->withToken($token);
        }

        return $http;
    }

    /**
     * Decode nested SQL Server JSON responses returned by the API.
     */
    private function decodeNestedJson(mixed $raw): array
    {
        if (is_array($raw) && count($raw) > 0 && isset($raw[0][self::JSON_KEY])) {
            $inner = $raw[0][self::JSON_KEY];

            return json_decode((string) $inner, true) ?? [];
        }

        if (is_string($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return (array) $raw;
    }

    public function getPlansByFilter(PlanFilterDTO $filter): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Fetching plans by filter', [
            'customerCode' => $filter->customerCode,
            'zone' => $filter->zone,
            'territory' => $filter->territory,
            'season' => $filter->season,
            'onlyClosed' => $filter->onlyClosed,
        ]);

        try {
            $customerCode = $filter->customerCode;
            if (empty($customerCode)) {
                return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
            }

            $response = $this->client()->get("/planes-cliente/{$customerCode}", [
                'soloCerrados' => $filter->onlyClosed ? 1 : 0,
                'zona' => $filter->zone,
                'territorio' => $filter->territory,
                'temporada' => $filter->season,
            ]);

            if ($response->status() === 404) {
                Log::info('[HttpFertilizationPlanRepository] No plans found for customer (404)', [
                    'customerCode' => $customerCode,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
            }

            if (! $response->successful()) {
                Log::warning('[HttpFertilizationPlanRepository] Error fetching plans from API', [
                    'customerCode' => $customerCode,
                    'status' => $response->status(),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
            }

            $body = $response->json();
            $decoded = $body['planes'] ?? $body['data'] ?? $this->decodeNestedJson($body);
            if (! is_array($decoded)) {
                $decoded = [];
            }

            $plans = [];
            foreach ($decoded as $item) {
                $statusStr = $item['U_Status'] ?? $item['Estado'] ?? $item['Status'] ?? '1';
                $status = match ((string) $statusStr) {
                    '1', 'Activo' => PlanStatusEnum::ACTIVE,
                    '0', 'Borrador' => PlanStatusEnum::DRAFT,
                    '2', 'Cerrado' => PlanStatusEnum::CLOSED,
                    '3', 'Cancelado' => PlanStatusEnum::CANCELLED,
                    default => PlanStatusEnum::ACTIVE,
                };

                $plans[] = new FertilizationPlan(
                    planCode: (string) ($item['Code'] ?? $item['PlanCode'] ?? $item['U_PlanCode'] ?? ''),
                    version: (int) ($item['U_Version'] ?? $item['Version'] ?? 1),
                    customerCode: (string) ($item['CardCode'] ?? $customerCode),
                    customerName: (string) ($item['Name'] ?? $item['CardName'] ?? ''),
                    advisorId: (string) ($item['SlpCode'] ?? $item['AdvisorId'] ?? ''),
                    advisorName: (string) ($item['SlpName'] ?? $item['AdvisorName'] ?? ''),
                    zone: (string) ($item['Zona'] ?? ''),
                    territory: (string) ($item['Territorio'] ?? $item['U_TerritorioId'] ?? ''),
                    season: (string) ($item['Temporada'] ?? ''),
                    hectares: (float) ($item['U_Superficie'] ?? $item['Hectareas'] ?? $item['U_Hectareas'] ?? 1.0),
                    soilType: (string) ($item['TipoSuelo'] ?? ''),
                    latitude: isset($item['Latitud']) ? (float) $item['Latitud'] : null,
                    longitude: isset($item['Longitud']) ? (float) $item['Longitud'] : null,
                    hasSoilAnalysis: (bool) ($item['TieneAnalisisSuelo'] ?? false),
                    soilAnalysisFilePath: $item['ArchivoSuelo'] ?? null,
                    hasTestigo: (bool) ($item['LlevaTestigo'] ?? false),
                    previousCrop: $item['CultivoAnterior'] ?? null,
                    previousCropYield: isset($item['RendimientoAnterior']) ? (float) $item['RendimientoAnterior'] : null,
                    currentCropCode: (string) ($item['U_Code_Cultivo'] ?? $item['CultivoActualCode'] ?? ''),
                    currentCropName: $item['nombrecultivo'] ?? $item['CultivoActual'] ?? null,
                    estimatedSowingDate: $item['U_Fecha_Siembra'] ?? $item['FechaSiembra'] ?? null,
                    targetYield: isset($item['MetaRendimiento']) ? (float) $item['MetaRendimiento'] : null,
                    status: $status,
                    createdAt: isset($item['CreateDate']) ? Carbon::parse((string) $item['CreateDate']) : Carbon::now(),
                );
            }

            Log::info('[HttpFertilizationPlanRepository] Plans retrieved for customer', [
                'customerCode' => $customerCode,
                'count' => count($plans),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [
                'data' => $plans,
                'total' => count($plans),
                'page' => $filter->page,
                'limit' => $filter->limit,
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] Error in getPlansByFilter', [
                'customerCode' => $filter->customerCode ?? null,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
        }
    }

    public function getPlanByCode(string $planCode, ?int $version = null): ?FertilizationPlan
    {
        try {
            $response = $this->client()->get('/plan-fertilizacion/datos-pdf', ['planCode' => $planCode]);

            if ($response->successful()) {
                $decoded = $this->decodeNestedJson($response->json());
                if (! empty($decoded)) {
                    $master = $decoded['maestro'] ?? $decoded['planInfo'] ?? $decoded;
                    $stagesRaw = $decoded['etapas'] ?? [];

                    $stages = [];
                    foreach ($stagesRaw as $st) {
                        $products = [];
                        foreach ($st['productos'] ?? [] as $prod) {
                            $products[] = new FertilizationProduct(
                                itemCode: (string) ($prod['ItemCode'] ?? ''),
                                itemName: (string) ($prod['ItemName'] ?? ''),
                                dosePerHectare: (float) ($prod['Dosis'] ?? $prod['DosisPorHa'] ?? 0.0),
                                unitOfMeasure: (string) ($prod['Unidad'] ?? 'Kg/Ha'),
                                unitPrice: (float) ($prod['Precio'] ?? 0.0),
                                activeIngredient: $prod['IngredienteActivo'] ?? null,
                                applicationMethod: $prod['Metodo'] ?? null,
                                isImpulsoraProduct: (bool) ($prod['EsImpulsora'] ?? true),
                            );
                        }

                        $stages[] = new PhenologicalStage(
                            stageId: (int) ($st['EtapaId'] ?? 0),
                            stageName: (string) ($st['NombreEtapa'] ?? ''),
                            order: (int) ($st['Orden'] ?? 1),
                            products: $products,
                            applicationMethod: $st['Metodo'] ?? null,
                            daysAfterSowing: isset($st['DiasDespuesSiembra']) ? (int) $st['DiasDespuesSiembra'] : null,
                        );
                    }

                    return new FertilizationPlan(
                        planCode: (string) ($master['PlanCode'] ?? $planCode),
                        version: (int) ($master['Version'] ?? $version ?? 1),
                        customerCode: (string) ($master['CardCode'] ?? ''),
                        customerName: (string) ($master['CardName'] ?? ''),
                        advisorId: (string) ($master['SlpCode'] ?? $master['AdvisorId'] ?? ''),
                        advisorName: (string) ($master['SlpName'] ?? $master['AdvisorName'] ?? ''),
                        zone: (string) ($master['Zona'] ?? ''),
                        territory: (string) ($master['Territorio'] ?? ''),
                        season: (string) ($master['Temporada'] ?? ''),
                        hectares: (float) ($master['Hectareas'] ?? 1.0),
                        soilType: (string) ($master['TipoSuelo'] ?? ''),
                        latitude: isset($master['Latitud']) ? (float) $master['Latitud'] : null,
                        longitude: isset($master['Longitud']) ? (float) $master['Longitud'] : null,
                        hasSoilAnalysis: (bool) ($master['TieneAnalisisSuelo'] ?? false),
                        soilAnalysisFilePath: $master['ArchivoSuelo'] ?? null,
                        hasTestigo: (bool) ($master['LlevaTestigo'] ?? false),
                        previousCrop: $master['CultivoAnterior'] ?? null,
                        previousCropYield: isset($master['RendimientoAnterior']) ? (float) $master['RendimientoAnterior'] : null,
                        currentCropCode: $master['CultivoActualCode'] ?? null,
                        currentCropName: $master['CultivoActual'] ?? null,
                        estimatedSowingDate: $master['FechaSiembra'] ?? null,
                        targetYield: isset($master['MetaRendimiento']) ? (float) $master['MetaRendimiento'] : null,
                        stages: $stages,
                        status: PlanStatusEnum::ACTIVE,
                        createdAt: Carbon::now(),
                    );
                }
            }

            // Fallback: Si el endpoint específico no responde o no existe, reconstruir desde el detalle del plan
            $raw = $this->getPlanForVersioning($planCode);
            if (! empty($raw['maestro'])) {
                $master = $raw['maestro'];
                $stagesRaw = $raw['etapas'] ?? [];

                $catalogs = app(CatalogsRepositoryInterface::class);
                $prodsCatalog = $catalogs->getProducts();
                $catByCode = collect($prodsCatalog)->keyBy('ItemCode')->all();

                $stages = [];
                foreach ($stagesRaw as $st) {
                    $products = [];
                    foreach ($st['listaProductos'] ?? [] as $lp) {
                        $itemCode = (string) ($lp['id'] ?? $lp['ItemCode'] ?? '');
                        $dose = (float) ($lp['dosis'] ?? $lp['Dosis'] ?? 0.0);
                        $catItem = $catByCode[$itemCode] ?? null;

                        $products[] = new FertilizationProduct(
                            itemCode: $itemCode,
                            itemName: (string) ($catItem['ItemName'] ?? $itemCode),
                            dosePerHectare: $dose,
                            unitOfMeasure: 'Kg/Ha',
                            unitPrice: 0.0,
                            activeIngredient: $catItem['U_Det_IngActivo'] ?? null,
                            applicationMethod: null,
                            isImpulsoraProduct: true,
                        );
                    }

                    $stages[] = new PhenologicalStage(
                        stageId: (int) ($st['codeEtapa'] ?? 0),
                        stageName: (string) ($st['nameEtapa'] ?? ''),
                        order: (int) ($st['codeEtapa'] ?? 1),
                        products: $products,
                        applicationMethod: null,
                    );
                }

                $coords = isset($master['Coordenadas']) ? explode(',', (string) $master['Coordenadas']) : null;
                $lat = $coords && isset($coords[0]) && is_numeric(trim($coords[0])) ? (float) trim($coords[0]) : null;
                $lon = $coords && isset($coords[1]) && is_numeric(trim($coords[1])) ? (float) trim($coords[1]) : null;

                $cropCode = (string) ($master['CodeCultivo'] ?? '');
                $crops = $catalogs->getCropTypes();
                $cropName = collect($crops)->firstWhere('Code', $cropCode)['Name'] ?? 'Cultivo';

                return new FertilizationPlan(
                    planCode: (string) ($master['CodigoPlan'] ?? $planCode),
                    version: (int) ($master['VersionActual'] ?? $version ?? 1),
                    customerCode: (string) ($master['CardCode'] ?? ''),
                    customerName: (string) ($master['NombrePlan'] ?? ''),
                    advisorId: (string) ($master['IDAsesorAgr'] ?? $master['IDAsesorVenta'] ?? ''),
                    advisorName: '',
                    zone: '',
                    territory: '',
                    season: '',
                    hectares: (float) ($master['Superficie'] ?? 1.0),
                    soilType: (string) ($master['CodeTipoSuelo'] ?? ''),
                    latitude: $lat,
                    longitude: $lon,
                    hasSoilAnalysis: ($master['AnalisisSuelo'] ?? '') === 'SI',
                    soilAnalysisFilePath: null,
                    hasTestigo: false,
                    previousCrop: isset($master['CodeCultAnterior']) ? (string) $master['CodeCultAnterior'] : null,
                    previousCropYield: isset($master['RendimientoReal']) ? (float) $master['RendimientoReal'] : null,
                    currentCropCode: $cropCode,
                    currentCropName: $cropName,
                    estimatedSowingDate: $master['FechaSiembra'] ?? null,
                    targetYield: isset($master['ObjetivoProd']) ? (float) $master['ObjetivoProd'] : null,
                    stages: $stages,
                    status: PlanStatusEnum::ACTIVE,
                    createdAt: Carbon::now(),
                );
            }

            return null;
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getPlanByCode error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function createPlan(CreatePlanDTO $dto): array
    {
        try {
            $normalizedStages = [];
            foreach ($dto->stages as $index => $stage) {
                $stageProducts = [];
                foreach ($stage['products'] ?? [] as $prod) {
                    $itemCode = (string) ($prod['itemCode'] ?? $prod['ItemCode'] ?? $prod['codigo'] ?? '');
                    $itemName = (string) ($prod['itemName'] ?? $prod['ItemName'] ?? $prod['nombre'] ?? '');
                    $dose = (float) ($prod['dosePerHectare'] ?? $prod['dosis'] ?? $prod['Dosis'] ?? 0.0);
                    $price = (float) ($prod['unitPrice'] ?? $prod['precio'] ?? $prod['Precio'] ?? 0.0);
                    $unit = (string) ($prod['unitOfMeasure'] ?? $prod['unidad'] ?? $prod['Unidad'] ?? 'Kg/Ha');
                    $method = (string) ($prod['applicationMethod'] ?? $prod['metodo'] ?? $prod['Metodo'] ?? ($stage['applicationMethod'] ?? 'Tradicional'));

                    $stageProducts[] = [
                        'itemCode' => $itemCode,
                        'ItemCode' => $itemCode,
                        'codigo' => $itemCode,
                        'itemName' => $itemName,
                        'ItemName' => $itemName,
                        'nombre' => $itemName,
                        'dosePerHectare' => $dose,
                        'dosis' => $dose,
                        'Dosis' => $dose,
                        'unitPrice' => $price,
                        'precio' => $price,
                        'Precio' => $price,
                        'unitOfMeasure' => $unit,
                        'unidad' => $unit,
                        'Unidad' => $unit,
                        'applicationMethod' => $method,
                        'metodo' => $method,
                        'Metodo' => $method,
                    ];
                }

                $stageName = (string) ($stage['stageName'] ?? $stage['nombreEtapa'] ?? $stage['NombreEtapa'] ?? ('Etapa '.($index + 1)));
                $stageId = (int) ($stage['stageId'] ?? $stage['idEtapa'] ?? $stage['EtapaId'] ?? ($index + 1));
                $order = (int) ($stage['orderIndex'] ?? $stage['orden'] ?? $stage['Orden'] ?? ($index + 1));
                $appDate = ! empty($stage['applicationDate']) ? (string) $stage['applicationDate'] : (! empty($stage['fechaAplicacion']) ? (string) $stage['fechaAplicacion'] : null);
                $appMethod = (string) ($stage['applicationMethod'] ?? $stage['metodo'] ?? $stage['Metodo'] ?? 'Tradicional');
                $observations = (string) ($stage['observations'] ?? $stage['comentarios'] ?? $stage['observaciones'] ?? $stage['Comentarios'] ?? '');

                $normalizedStages[] = [
                    'stageId' => $stageId,
                    'idEtapa' => $stageId,
                    'EtapaId' => $stageId,
                    'stageName' => $stageName,
                    'nombreEtapa' => $stageName,
                    'NombreEtapa' => $stageName,
                    'orderIndex' => $order,
                    'orden' => $order,
                    'Orden' => $order,
                    'applicationDate' => $appDate,
                    'fechaAplicacion' => $appDate,
                    'FechaAplicacion' => $appDate,
                    'applicationMethod' => $appMethod,
                    'metodo' => $appMethod,
                    'Metodo' => $appMethod,
                    'observations' => $observations,
                    'comentarios' => $observations,
                    'observaciones' => $observations,
                    'Comentarios' => $observations,
                    'products' => $stageProducts,
                    'productos' => $stageProducts,
                ];
            }

            $coordenadas = ($dto->latitude !== null && $dto->longitude !== null)
                ? "{$dto->latitude}, {$dto->longitude}"
                : null;

            $soilCodeMap = [
                'franco' => 1,
                'limoso' => 2,
                'arcilloso' => 3,
                'arenoso' => 4,
            ];
            $rawSoil = trim(strtolower((string) $dto->soilType));
            $soilCode = is_numeric($dto->soilType) ? (int) $dto->soilType : ($soilCodeMap[$rawSoil] ?? 1);
            $soilName = match ($soilCode) {
                1 => 'Franco',
                2 => 'Limoso',
                3 => 'Arcilloso',
                4 => 'Arenoso',
                default => 'Franco',
            };

            $maestro = [
                // Identificadores y Asesor
                'CardCode' => $dto->customerCode,
                'cardCode' => $dto->customerCode,
                'CardName' => $dto->customerName,
                'cardName' => $dto->customerName,
                'IDAsesorVenta' => ! empty($dto->salesAdvisorId) ? $dto->salesAdvisorId : (string) (session('authenticated_user')?->id ?? $dto->advisorId),
                'IDAsesorAgr' => $dto->advisorId,
                'SlpCode' => ! empty($dto->salesAdvisorId) ? $dto->salesAdvisorId : (string) (session('authenticated_user')?->id ?? $dto->advisorId),
                'slpCode' => ! empty($dto->salesAdvisorId) ? $dto->salesAdvisorId : (string) (session('authenticated_user')?->id ?? $dto->advisorId),
                'AdvisorId' => $dto->advisorId,
                'advisorId' => $dto->advisorId,
                'Asesor' => $dto->advisorName,
                'AdvisorName' => $dto->advisorName,
                'advisorName' => $dto->advisorName,
                'AsesorVenta' => ! empty($dto->salesAdvisorName) ? $dto->salesAdvisorName : (string) (session('authenticated_user')?->name ?? 'Asesor Comercial'),
                'AsesorVentas' => ! empty($dto->salesAdvisorName) ? $dto->salesAdvisorName : (string) (session('authenticated_user')?->name ?? 'Asesor Comercial'),
                'NombreAsesorVenta' => ! empty($dto->salesAdvisorName) ? $dto->salesAdvisorName : (string) (session('authenticated_user')?->name ?? 'Asesor Comercial'),
                'NombreAsesorAgr' => $dto->advisorName,
                'SlpName' => ! empty($dto->salesAdvisorName) ? $dto->salesAdvisorName : (string) (session('authenticated_user')?->name ?? 'Asesor Comercial'),
                'slpName' => ! empty($dto->salesAdvisorName) ? $dto->salesAdvisorName : (string) (session('authenticated_user')?->name ?? 'Asesor Comercial'),

                // Parcela, Superficie y Ubicación
                'Parcela' => $dto->parcela,
                'parcela' => $dto->parcela,
                'U_Parcela' => $dto->parcela,
                'Superficie' => $dto->hectares,
                'superficie' => $dto->hectares,
                'Hectareas' => $dto->hectares,
                'hectareas' => $dto->hectares,
                'Zona' => $dto->zone,
                'zona' => $dto->zone,
                'Territorio' => $dto->territory,
                'territorio' => $dto->territory,
                'Temporada' => $dto->season,
                'temporada' => $dto->season,
                'CodeTipoSuelo' => $soilCode,
                'TipoSuelo' => $soilName,
                'tipoSuelo' => $soilName,
                'soilType' => $soilName,

                // Coordenadas
                'Coordenadas' => $coordenadas,
                'coordenadas' => $coordenadas,
                'Latitud' => $dto->latitude,
                'latitud' => $dto->latitude,
                'Longitud' => $dto->longitude,
                'longitud' => $dto->longitude,

                // Análisis y Testigo
                'AnalisisSuelo' => $dto->hasSoilAnalysis ? 'SI' : 'NO',
                'analisisSuelo' => $dto->hasSoilAnalysis ? 'SI' : 'NO',
                'TieneAnalisisSuelo' => $dto->hasSoilAnalysis,
                'tieneAnalisisSuelo' => $dto->hasSoilAnalysis,
                'LlevaTestigo' => $dto->hasTestigo ? 'SI' : 'NO',
                'llevaTestigo' => $dto->hasTestigo,

                // Cultivos y Rendimientos
                'CodeCultAnterior' => $dto->previousCrop,
                'CultivoAnterior' => $dto->previousCrop,
                'cultivoAnterior' => $dto->previousCrop,
                'RendimientoReal' => $dto->previousCropYield,
                'RendimientoAnterior' => $dto->previousCropYield,
                'rendimientoAnterior' => $dto->previousCropYield,
                'RendimientoMax' => $dto->previousCropMaxYield,
                'RendimientoMaxRef' => $dto->previousCropMaxYield,
                'rendimientoMaxRef' => $dto->previousCropMaxYield,
                'CodeCultivo' => $dto->currentCropCode,
                'CultivoActual' => $dto->currentCropCode,
                'cultivoActual' => $dto->currentCropCode,
                'CultivoActualName' => $dto->currentCropName,
                'cultivoActualName' => $dto->currentCropName,
                'FechaSiembra' => $dto->estimatedSowingDate,
                'fechaSiembra' => $dto->estimatedSowingDate,
                'VariedadSemilla' => $dto->seedVariety,
                'variedadSemilla' => $dto->seedVariety,
                'Variedad' => $dto->seedVariety,
                'variedad' => $dto->seedVariety,
                'ObjetivoProd' => $dto->targetYield,
                'MetaRendimiento' => $dto->targetYield,
                'metaRendimiento' => $dto->targetYield,
                'SistemaRiego' => $dto->irrigationSystem,
                'sistemaRiego' => $dto->irrigationSystem,
            ];

            $payload = [
                // Bloque maestro requerido por Api-PlanFertilizacion
                'maestro' => $maestro,

                // Identificadores de Cliente y Asesor en raíz para compatibilidad
                'cardCode' => $dto->customerCode,
                'CardCode' => $dto->customerCode,
                'customerCode' => $dto->customerCode,
                'cardName' => $dto->customerName,
                'CardName' => $dto->customerName,
                'customerName' => $dto->customerName,
                'advisorId' => $dto->advisorId,
                'AdvisorId' => $dto->advisorId,
                'slpCode' => $dto->advisorId,
                'SlpCode' => $dto->advisorId,
                'advisorName' => $dto->advisorName,
                'AdvisorName' => $dto->advisorName,
                'slpName' => $dto->advisorName,
                'SlpName' => $dto->advisorName,

                // Datos de Parcela y Suelo
                'parcela' => $dto->parcela,
                'Parcela' => $dto->parcela,
                'U_Parcela' => $dto->parcela,
                'zona' => $dto->zone,
                'Zona' => $dto->zone,
                'U_Zona' => $dto->zone,
                'zone' => $dto->zone,
                'territorio' => $dto->territory,
                'Territorio' => $dto->territory,
                'U_Territorio' => $dto->territory,
                'territory' => $dto->territory,
                'temporada' => $dto->season,
                'Temporada' => $dto->season,
                'U_Temporada' => $dto->season,
                'season' => $dto->season,
                'hectareas' => $dto->hectares,
                'Hectareas' => $dto->hectares,
                'U_Hectareas' => $dto->hectares,
                'CodeTipoSuelo' => $soilCode,
                'tipoSuelo' => $soilName,
                'TipoSuelo' => $soilName,
                'U_TipoSuelo' => $soilName,
                'soilType' => $soilName,

                // Georreferenciación
                'latitud' => $dto->latitude,
                'Latitud' => $dto->latitude,
                'U_Latitud' => $dto->latitude,
                'latitude' => $dto->latitude,
                'longitud' => $dto->longitude,
                'Longitud' => $dto->longitude,
                'U_Longitud' => $dto->longitude,
                'longitude' => $dto->longitude,
                'coordenadas' => $coordenadas,
                'Coordenadas' => $coordenadas,

                // Análisis y Testigo
                'tieneAnalisisSuelo' => $dto->hasSoilAnalysis,
                'analisisSuelo' => $dto->hasSoilAnalysis ? 'SI' : 'NO',
                'U_AnalisisSuelo' => $dto->hasSoilAnalysis ? 'SI' : 'NO',
                'archivoSuelo' => $dto->soilAnalysisFileName,
                'archivoSueloBase64' => $dto->soilAnalysisBase64,
                'llevaTestigo' => $dto->hasTestigo,
                'U_LlevaTestigo' => $dto->hasTestigo ? 'SI' : 'NO',

                // Cultivos y Rendimientos
                'cultivoAnterior' => $dto->previousCrop,
                'CultivoAnterior' => $dto->previousCrop,
                'U_CultivoAnterior' => $dto->previousCrop,
                'rendimientoAnterior' => $dto->previousCropYield,
                'RendimientoAnterior' => $dto->previousCropYield,
                'U_RendimientoAnterior' => $dto->previousCropYield,
                'rendimientoMaxRef' => $dto->previousCropMaxYield,
                'RendimientoMaxRef' => $dto->previousCropMaxYield,
                'U_RendimientoMaxRef' => $dto->previousCropMaxYield,
                'cultivoActual' => $dto->currentCropCode,
                'CultivoActual' => $dto->currentCropCode,
                'U_CultivoActual' => $dto->currentCropCode,
                'cultivoActualName' => $dto->currentCropName,
                'seedVariety' => $dto->seedVariety,
                'variedadSemilla' => $dto->seedVariety,
                'VariedadSemilla' => $dto->seedVariety,
                'variedad' => $dto->seedVariety,
                'Variedad' => $dto->seedVariety,
                'U_Variedad' => $dto->seedVariety,
                'U_VariedadSemilla' => $dto->seedVariety,
                'fechaSiembra' => $dto->estimatedSowingDate,
                'FechaSiembra' => $dto->estimatedSowingDate,
                'U_FechaSiembra' => $dto->estimatedSowingDate,
                'metaRendimiento' => $dto->targetYield,
                'MetaRendimiento' => $dto->targetYield,
                'U_MetaRendimiento' => $dto->targetYield,
                'sistemaRiego' => $dto->irrigationSystem,

                // Propuesta de Etapas
                'etapas' => $normalizedStages,
                'stages' => $normalizedStages,
            ];

            $logPayload = $payload;
            if (! empty($logPayload['archivoSueloBase64']) && strlen((string) $logPayload['archivoSueloBase64']) > 100) {
                $logPayload['archivoSueloBase64'] = substr((string) $logPayload['archivoSueloBase64'], 0, 50).'... [TRUNCATED '.strlen((string) $payload['archivoSueloBase64']).' BYTES]';
            }

            Log::info('[HttpFertilizationPlanRepository] Enviando payload a /plan-fertilizacion/guardar', [
                'endpoint' => $this->baseUrl.'/plan-fertilizacion/guardar',
                'payload' => $logPayload,
            ]);

            $response = $this->client()->post('/plan-fertilizacion/guardar', $payload);

            Log::info('[HttpFertilizationPlanRepository] Respuesta recibida de API /plan-fertilizacion/guardar', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->successful()) {
                $raw = $response->json();
                $decoded = $this->decodeNestedJson($raw);

                if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    $decoded = $decoded[0];
                }

                $estado = $decoded['estado'] ?? $decoded['Estado'] ?? $raw['estado'] ?? 0;
                $mensaje = $decoded['mensaje'] ?? $decoded['Mensaje'] ?? $raw['mensaje'] ?? '¡Plan guardado exitosamente!';
                $planCode = $decoded['planCode'] ?? $decoded['PlanCode'] ?? $decoded['codigo'] ?? $decoded['Codigo'] ?? $raw['planCode'] ?? null;

                if ((int) $estado === 1) {
                    Log::error('[HttpFertilizationPlanRepository] Error devuelto por SQL Server al guardar plan', [
                        'estado' => $estado,
                        'mensaje' => $mensaje,
                    ]);

                    return [
                        'success' => false,
                        'message' => $mensaje,
                    ];
                }

                return [
                    'success' => true,
                    'message' => $mensaje,
                    'planCode' => $planCode,
                ];
            }

            Log::error('[HttpFertilizationPlanRepository] Error devuelto por API al guardar plan', [
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? $response->json('message') ?? ('Error al guardar el plan vía API (HTTP '.$response->status().').'),
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] Excepción al guardar plan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function versionPlan(string $planCode): array
    {
        try {
            $response = $this->client()->post('/plan-fertilizacion/versionar', ['planCode' => $planCode]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => $data['mensaje'] ?? '¡Nueva versión generada exitosamente!',
                    'planCode' => $data['planCode'] ?? $planCode,
                    'newVersion' => $data['nuevaVersion'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? 'Error al versionar plan.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] versionPlan error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelPlan(string $planCode, string $comment): array
    {
        Log::info('[HttpFertilizationPlanRepository] Sending cancel plan request to API', [
            'planCode' => $planCode,
            'comment' => $comment,
        ]);

        try {
            $response = $this->client()->post("/planes-cliente/{$planCode}/cancelar", [
                'comentario' => $comment,
            ]);

            Log::info('[HttpFertilizationPlanRepository] Cancel plan response received', [
                'planCode' => $planCode,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => $data['mensaje'] ?? 'Plan cancelado exitosamente.',
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? 'Error al cancelar plan.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] cancelPlan error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function saveTracking(UpdateTrackingDTO $dto): array
    {
        try {
            $rawRecords = $dto->getRecords();
            $formattedStages = [];

            foreach ($rawRecords as $st) {
                $prods = [];
                foreach ($st['productos'] ?? [] as $p) {
                    $isImpulsora = ($p['tipo'] ?? 'impulsora') === 'impulsora';
                    $prods[] = [
                        'id' => $isImpulsora ? ($p['idProducto'] ?? $p['id'] ?? '') : ($p['nombreProducto'] ?? $p['id'] ?? ''),
                        'dosis' => (float) ($p['dosis'] ?? $p['dosisReal'] ?? 0.0),
                        'Precio' => (float) ($p['Precio'] ?? $p['precio'] ?? 0.0),
                        'Proveedor' => $isImpulsora ? 'GRUPO IMPULSORA' : (string) ($p['Proveedor'] ?? $p['proveedor'] ?? ''),
                        'tipo' => $p['tipo'] ?? ($isImpulsora ? 'impulsora' : 'otros'),
                    ];
                }

                $formattedStages[] = [
                    'codeEtapa' => (int) ($st['codeEtapa'] ?? $st['idEtapa'] ?? 0),
                    'nameEtapa' => (string) ($st['nameEtapa'] ?? $st['nombreEtapa'] ?? ''),
                    'fechaAplicacion' => (string) ($st['fechaAplicacion'] ?? date('Y-m-d')),
                    'idAplicacion' => isset($st['idAplicacion']) && $st['idAplicacion'] !== '' ? (int) $st['idAplicacion'] : (isset($st['idMetodo']) && $st['idMetodo'] !== '' ? (int) $st['idMetodo'] : 1),
                    'comentario' => (string) ($st['comentario'] ?? $st['comentarios'] ?? ''),
                    'productos' => $prods,
                ];
            }

            Log::info('[HttpFertilizationPlanRepository] Sending tracking save request to API', [
                'planCode' => $dto->planCode,
                'stages_count' => count($formattedStages),
            ]);

            $response = $this->client()->post("/planes-cliente/{$dto->planCode}/seguimiento", [
                'etapas' => $formattedStages,
            ]);

            Log::info('[HttpFertilizationPlanRepository] Tracking save response received', [
                'planCode' => $dto->planCode,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => $data['mensaje'] ?? 'Seguimiento guardado exitosamente.',
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? 'Error al guardar seguimiento.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] saveTracking error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function captureResults(CaptureResultDTO $dto): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Sending capture results to API', [
            'planCode' => $dto->planCode,
            'yield' => $dto->finalYieldTonPerHa,
            'costPerHa' => $dto->costPerHectare,
            'harvestDate' => $dto->harvestDate,
        ]);

        try {
            $formattedYield = number_format(min($dto->finalYieldTonPerHa, 99.9), 1, '.', '');
            $formattedCostTotal = number_format($dto->costPerHectare, 2, '.', '');
            $formattedCostTrad = number_format($dto->traditionalCost, 2, '.', '');
            $formattedDose = number_format($dto->dose, 2, '.', '');
            $formattedYieldTrad = number_format($dto->traditionalYield, 2, '.', '');
            $formattedPrice = number_format($dto->harvestPrice, 2, '.', '');
            $formattedExtraProfit = number_format($dto->extraProfit, 2, '.', '');
            $formattedExtraInvest = number_format($dto->extraInvestment, 2, '.', '');
            $formattedRoi = number_format($dto->returnOnInvestment, 2, '.', '');

            $coords = $dto->coordinates;
            if (empty($coords)) {
                $coords = '25.790466,-108.995888';
            }

            $response = $this->client()->post("/planes-cliente/{$dto->planCode}/guardar-resultados", [
                'U_FechaCaptura' => $dto->harvestDate ?? date('Y-m-d'),
                'U_Rendimiento' => (float) $formattedYield,
                'U_CostoTotal' => (float) $formattedCostTotal,
                'U_Costo_Trad' => (float) $formattedCostTrad,
                'U_Dosis' => (float) $formattedDose,
                'U_Rendimiento_Trad' => (float) $formattedYieldTrad,
                'U_Precio_Cosecha' => (float) $formattedPrice,
                'U_Utilidad_Ext' => (float) $formattedExtraProfit,
                'U_Inversion_Ext' => (float) $formattedExtraInvest,
                'U_Retorno_Inv' => (float) $formattedRoi,
                'U_Recomendaciones' => mb_substr($dto->recommendations ?: 'Recomendaciones agronómicas registradas.', 0, 500),
                'U_Propuesta' => mb_substr($dto->proposal ?: 'Propuesta de fertilización aplicada.', 0, 500),
                'U_BuenasPracticas' => mb_substr($dto->goodPractices ?: 'Buenas prácticas agrícolas implementadas.', 0, 500),
                'U_Coordenadas' => mb_substr($coords, 0, 500),
            ]);

            Log::info('[HttpFertilizationPlanRepository] Capture results API response received', [
                'planCode' => $dto->planCode,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => $data['mensaje'] ?? 'Resultados guardados exitosamente.',
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? 'Error al guardar resultados.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] captureResults error', [
                'planCode' => $dto->planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getProposalPdfData(string $planCode, int $version): array
    {
        try {
            $response = $this->client()->get('/plan-fertilizacion/datos-pdf', [
                'planCode' => $planCode,
                'version' => $version,
            ]);

            if ($response->successful()) {
                $decoded = $this->decodeNestedJson($response->json());
                if (! empty($decoded)) {
                    return $decoded;
                }
            }

            return $this->buildProposalPdfDataFromPlan($planCode, $version);
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getProposalPdfData error', ['error' => $e->getMessage()]);

            return $this->buildProposalPdfDataFromPlan($planCode, $version);
        }
    }

    private function buildProposalPdfDataFromPlan(string $planCode, int $version): array
    {
        try {
            $raw = $this->getPlanForVersioning($planCode);
            if (empty($raw) || empty($raw['maestro'])) {
                return [];
            }

            $maestro = $raw['maestro'];
            $etapasRaw = $raw['etapas'] ?? [];

            $superficie = (float) ($maestro['Superficie'] ?? 1.0);
            $cropCode = (string) ($maestro['CodeCultivo'] ?? '1');

            $catalogs = app(CatalogsRepositoryInterface::class);
            $crops = $catalogs->getCropTypes();
            $cropName = collect($crops)->firstWhere('Code', $cropCode)['Name'] ?? 'Maíz';

            $prodsCatalog = $catalogs->getProducts();
            $catByCode = collect($prodsCatalog)->keyBy('ItemCode')->all();

            $methods = $catalogs->getApplicationMethods();
            $methodsByCode = collect($methods)->keyBy('Code')->all();

            $stagesFormatted = [];
            foreach ($etapasRaw as $st) {
                $appId = (string) ($st['idAplicacion'] ?? '1');
                $methodName = $methodsByCode[$appId]['Name'] ?? 'Tradicional';
                $prods = [];

                foreach ($st['listaProductos'] ?? [] as $lp) {
                    $itemCode = (string) ($lp['id'] ?? $lp['ItemCode'] ?? '');
                    $dose = (float) ($lp['dosis'] ?? $lp['Dosis'] ?? 0.0);
                    $catItem = $catByCode[$itemCode] ?? null;
                    $itemName = $catItem['ItemName'] ?? $itemCode;
                    $activeIng = $catItem['U_Det_IngActivo'] ?? 'No especificado';
                    $estPurchase = $superficie * $dose;

                    $prods[] = [
                        'itemCode' => $itemCode,
                        'ItemCode' => $itemCode,
                        'itemName' => $itemName,
                        'ItemName' => $itemName,
                        'producto' => $itemName,
                        'dosePerHectare' => $dose,
                        'dosis' => $dose,
                        'compraEstimada' => number_format($estPurchase, 2, '.', ','),
                        'unitOfMeasure' => 'Kg/Ha',
                        'unidad' => 'Kg/Ha',
                        'activeIngredient' => $activeIng,
                        'ingredienteActivo' => $activeIng,
                        'applicationMethod' => $methodName,
                        'metodo' => $methodName,
                        'fechaEstimada' => $st['fechaAplicacion'] ?? null,
                    ];
                }

                $stageName = (string) ($st['nameEtapa'] ?? $st['NombreEtapa'] ?? ('Etapa '.($st['codeEtapa'] ?? 1)));

                $stagesFormatted[] = [
                    'stageId' => (int) ($st['codeEtapa'] ?? 1),
                    'stageName' => $stageName,
                    'NombreEtapa' => $stageName,
                    'titulo' => $stageName,
                    'applicationDate' => $st['fechaAplicacion'] ?? null,
                    'fechaEstimada' => $st['fechaAplicacion'] ?? null,
                    'applicationMethod' => $methodName,
                    'Metodo' => $methodName,
                    'observations' => (string) ($st['comentario'] ?? ''),
                    'recomendaciones' => (string) ($st['comentario'] ?? ''),
                    'productos' => $prods,
                    'products' => $prods,
                ];
            }

            // Nombre de asesor y cliente
            $authUserName = session('authenticated_user')?->name ?? 'Asesor Agronómico';
            if (str_contains($authUserName, '@')) {
                $cleanPrefix = explode('@', $authUserName)[0];
                $words = preg_split('/[._-]/', $cleanPrefix);
                $authUserName = ucwords(implode(' ', array_filter($words ?: [$cleanPrefix])));
            }

            // Asesor Técnico real desde el plan maestro
            $advisorTecnico = (string) (
                $maestro['NombreAsesorAgr'] ??
                $maestro['asesorTecnico'] ??
                $maestro['tecnicoCampo'] ??
                $maestro['Asesor'] ??
                $maestro['AdvisorName'] ??
                $maestro['U_Asesor'] ??
                $maestro['SlpName'] ??
                ''
            );
            if (empty($advisorTecnico) || str_contains($advisorTecnico, '@')) {
                $advisorTecnico = (! empty($authUserName) && ! str_contains($authUserName, '@')) ? $authUserName : 'Asesor Técnico';
            }

            // Asesor de Ventas real desde el plan maestro o usuario en sesión
            $advisorVentas = (string) (
                $maestro['NombreAsesorVenta'] ??
                $maestro['asesorVentas'] ??
                $maestro['AsesorVenta'] ??
                $maestro['SlpName'] ??
                ''
            );
            if (str_contains($advisorVentas, '@')) {
                $cleanPrefix = explode('@', $advisorVentas)[0];
                $words = preg_split('/[._-]/', $cleanPrefix);
                $advisorVentas = ucwords(implode(' ', array_filter($words ?: [$cleanPrefix])));
            }
            if (empty($advisorVentas) || $advisorVentas === $advisorTecnico) {
                $advisorVentas = ! empty($authUserName) ? $authUserName : 'Asesor Comercial';
            }

            $customerName = (string) ($maestro['NombrePlan'] ?? "Plan #{$planCode}");
            if (preg_match('/^\d+\s*-\s*(.+?)\s*-\s*[A-Z0-9]+$/', $customerName, $matches)) {
                $customerName = trim($matches[1]);
            }

            $soilCode = (int) ($maestro['CodeTipoSuelo'] ?? 1);
            $soilTypes = [1 => 'Franco', 2 => 'Limoso', 3 => 'Arcilloso', 4 => 'Arenoso'];
            $soilName = $soilTypes[$soilCode] ?? 'Franco';

            $parcela = (string) ($maestro['Parcela'] ?? 'General');
            $cardCode = (string) ($maestro['CardCode'] ?? '');
            $planMessage = ! empty($maestro['NombrePlan']) ? (string) $maestro['NombrePlan'] : "{$planCode} - Plan {$parcela} - {$cardCode}";

            return [
                'planCode' => $planCode,
                'PlanCode' => $planCode,
                'id' => $planCode,
                'version' => $version,
                'Version' => $version,
                'mensaje' => $planMessage,
                'customerName' => $customerName,
                'productor' => $customerName,
                'CardName' => $customerName,
                'CardCode' => $cardCode,
                'cardCode' => $cardCode,
                'advisorName' => $advisorTecnico,
                'asesorVentas' => $advisorVentas,
                'asesorTecnico' => $advisorTecnico,
                'tecnicoCampo' => $advisorTecnico,
                'SlpName' => $advisorVentas,
                'territorio' => 'Valle del Fuerte',
                'Territorio' => 'Valle del Fuerte',
                'parcela' => $parcela,
                'Parcela' => $parcela,
                'superficie' => $superficie,
                'Hectareas' => $superficie,
                'cultivo' => $cropName,
                'CultivoActual' => $cropName,
                'fechaSiembra' => $maestro['FechaSiembra'] ?? null,
                'FechaSiembra' => $maestro['FechaSiembra'] ?? null,
                'semilla' => (string) ($maestro['VariedadSemilla'] ?? 'N/A'),
                'seedVariety' => (string) ($maestro['VariedadSemilla'] ?? 'N/A'),
                'objetivoProduccion' => (float) ($maestro['ObjetivoProd'] ?? 0),
                'coordenadas' => $maestro['Coordenadas'] ?? null,
                'Coordenadas' => $maestro['Coordenadas'] ?? null,
                'TipoSuelo' => $soilName,
                'etapas' => $stagesFormatted,
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] Error construyendo propuesta PDF desde plan', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getResultsPdfData(string $planCode): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Fetching results PDF data from API', [
            'planCode' => $planCode,
        ]);

        try {
            $response = $this->client()->get('/plan-resultados/datos-pdf', [
                'planCode' => $planCode,
            ]);

            if ($response->successful()) {
                $decoded = $this->decodeNestedJson($response->json());
                Log::info('[HttpFertilizationPlanRepository] Results PDF data retrieved', [
                    'planCode' => $planCode,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return $decoded;
            }

            Log::warning('[HttpFertilizationPlanRepository] getResultsPdfData returned non-successful response', [
                'planCode' => $planCode,
                'status' => $response->status(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getResultsPdfData error', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [];
        }
    }

    public function savePlanPdf(string $planCode, int $version, string $status, string $base64File): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Saving plan PDF to database via API', [
            'planCode' => $planCode,
            'version' => $version,
            'status' => $status,
            'bytes' => strlen($base64File),
        ]);

        try {
            $response = $this->client()->post('/plan-fertilizacion/guardar-pdf', [
                'planCode' => $planCode,
                'version' => $version,
                'status' => $status,
                'archivoBase64' => $base64File,
            ]);

            $success = $response->successful();
            Log::info('[HttpFertilizationPlanRepository] Save plan PDF API response received', [
                'planCode' => $planCode,
                'status' => $response->status(),
                'success' => $success,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [
                'success' => $success,
                'message' => $response->json('mensaje') ?? 'PDF guardado.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] savePlanPdf error', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function saveResultsPdf(string $planCode, int $version, string $status, string $base64File): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Saving results PDF to database via API', [
            'planCode' => $planCode,
            'version' => $version,
            'status' => $status,
            'bytes' => strlen($base64File),
        ]);

        try {
            $response = $this->client()->post('/plan-resultados/guardar-pdf', [
                'planCode' => $planCode,
                'version' => $version,
                'status' => $status,
                'archivoBase64' => $base64File,
            ]);

            $success = $response->successful();
            Log::info('[HttpFertilizationPlanRepository] Save results PDF API response received', [
                'planCode' => $planCode,
                'status' => $response->status(),
                'success' => $success,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [
                'success' => $success,
                'message' => $response->json('mensaje') ?? 'PDF de resultados guardado.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] saveResultsPdf error', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTrackingStages(string $planCode, int $cropId): array
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Fetching tracking stages from API', [
            'planCode' => $planCode,
            'cropId' => $cropId,
        ]);

        try {
            $response = $this->client()->get('/plan-fertilizacion/etapas-seguimiento', [
                'cultivoId' => $cropId,
                'planCode' => $planCode,
            ]);

            if ($response->successful()) {
                $raw = $response->json();
                $decoded = $this->decodeNestedJson($raw);
                $stages = $decoded['etapas'] ?? (is_array($decoded) ? $decoded : []);

                Log::info('[HttpFertilizationPlanRepository] Tracking stages retrieved', [
                    'planCode' => $planCode,
                    'cropId' => $cropId,
                    'stages_count' => count($stages),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return $stages;
            }

            Log::warning('[HttpFertilizationPlanRepository] getTrackingStages returned non-successful response', [
                'planCode' => $planCode,
                'cropId' => $cropId,
                'status' => $response->status(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getTrackingStages error', [
                'planCode' => $planCode,
                'cropId' => $cropId,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return [];
        }
    }

    public function getPlanForVersioning(string $planCode): array
    {
        try {
            $response = $this->client()->get("/plan-fertilizacion/busquedaVersionar/{$planCode}");

            if ($response->successful()) {
                return $this->decodeNestedJson($response->json());
            }

            return [];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getPlanForVersioning error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function versionPlanFull(string $planCode, array $master, array $stages): array
    {
        try {
            $response = $this->client()->post('/plan-fertilizacion/versionar', [
                'planCode' => $planCode,
                'maestro' => $master,
                'etapas' => $stages,
            ]);

            if ($response->successful()) {
                $raw = $response->json();
                $decoded = $this->decodeNestedJson($raw);

                return [
                    'success' => ((int) ($decoded['estado'] ?? 0)) === 0,
                    'message' => $decoded['mensaje'] ?? '¡Nueva versión generada exitosamente!',
                    'planCode' => (string) ($decoded['planCode'] ?? $planCode),
                    'version' => (string) ($decoded['version'] ?? '2'),
                    'newVersion' => (string) ($decoded['version'] ?? '2'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('mensaje') ?? 'Error al versionar plan.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] versionPlanFull error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getStoredPlanPdf(string $planCode, int $version, string $status = 'Activo'): ?string
    {
        $startTime = microtime(true);
        Log::info('[HttpFertilizationPlanRepository] Checking stored plan PDF in DB via API', [
            'planCode' => $planCode,
            'version' => $version,
            'status' => $status,
        ]);

        try {
            $response = $this->client()->get('/plan-fertilizacion/pdf', [
                'planCode' => $planCode,
                'version' => $version,
                'status' => $status,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (! empty($data['U_Archivo_Base64'])) {
                    $base64 = (string) $data['U_Archivo_Base64'];
                    Log::info('[HttpFertilizationPlanRepository] Stored plan PDF found in DB', [
                        'planCode' => $planCode,
                        'version' => $version,
                        'bytes' => strlen($base64),
                        'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    ]);

                    return $base64;
                }
            }

            Log::info('[HttpFertilizationPlanRepository] Stored plan PDF not found in DB', [
                'planCode' => $planCode,
                'version' => $version,
                'status_code' => $response->status(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return null;
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getStoredPlanPdf error', [
                'planCode' => $planCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return null;
        }
    }
}

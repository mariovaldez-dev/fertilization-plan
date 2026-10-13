<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

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

            if (! $response->successful()) {
                Log::warning('[HttpFertilizationPlanRepository] Error fetching plans', ['status' => $response->status()]);

                return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
            }

            $body = $response->json();
            $decoded = $body['planes'] ?? $body['data'] ?? $this->decodeNestedJson($body);
            if (! is_array($decoded)) {
                $decoded = [];
            }

            $plans = [];
            foreach ($decoded as $item) {
                $statusStr = $item['Estado'] ?? $item['Status'] ?? 'Activo';
                $status = match ($statusStr) {
                    'Borrador' => PlanStatusEnum::DRAFT,
                    'Cerrado' => PlanStatusEnum::CLOSED,
                    'Cancelado' => PlanStatusEnum::CANCELLED,
                    default => PlanStatusEnum::ACTIVE,
                };

                $plans[] = new FertilizationPlan(
                    planCode: (string) ($item['PlanCode'] ?? $item['U_PlanCode'] ?? ''),
                    version: (int) ($item['Version'] ?? $item['U_Version'] ?? 1),
                    customerCode: (string) ($item['CardCode'] ?? $customerCode),
                    customerName: (string) ($item['CardName'] ?? ''),
                    advisorId: (string) ($item['SlpCode'] ?? $item['AdvisorId'] ?? ''),
                    advisorName: (string) ($item['SlpName'] ?? $item['AdvisorName'] ?? ''),
                    zone: (string) ($item['Zona'] ?? ''),
                    territory: (string) ($item['Territorio'] ?? ''),
                    season: (string) ($item['Temporada'] ?? ''),
                    hectares: (float) ($item['Hectareas'] ?? $item['U_Hectareas'] ?? 1.0),
                    soilType: (string) ($item['TipoSuelo'] ?? ''),
                    latitude: isset($item['Latitud']) ? (float) $item['Latitud'] : null,
                    longitude: isset($item['Longitud']) ? (float) $item['Longitud'] : null,
                    hasSoilAnalysis: (bool) ($item['TieneAnalisisSuelo'] ?? false),
                    soilAnalysisFilePath: $item['ArchivoSuelo'] ?? null,
                    hasTestigo: (bool) ($item['LlevaTestigo'] ?? false),
                    previousCrop: $item['CultivoAnterior'] ?? null,
                    previousCropYield: isset($item['RendimientoAnterior']) ? (float) $item['RendimientoAnterior'] : null,
                    currentCropCode: $item['CultivoActualCode'] ?? null,
                    currentCropName: $item['CultivoActual'] ?? null,
                    estimatedSowingDate: $item['FechaSiembra'] ?? null,
                    targetYield: isset($item['MetaRendimiento']) ? (float) $item['MetaRendimiento'] : null,
                    status: $status,
                    createdAt: isset($item['CreateDate']) ? Carbon::parse((string) $item['CreateDate']) : Carbon::now(),
                );
            }

            return [
                'data' => $plans,
                'total' => count($plans),
                'page' => $filter->page,
                'limit' => $filter->limit,
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] Error in getPlansByFilter', ['error' => $e->getMessage()]);

            return ['data' => [], 'total' => 0, 'page' => $filter->page, 'limit' => $filter->limit];
        }
    }

    public function getPlanByCode(string $planCode, ?int $version = null): ?FertilizationPlan
    {
        try {
            $response = $this->client()->get('/plan-fertilizacion/datos-pdf', ['planCode' => $planCode]);

            if (! $response->successful()) {
                return null;
            }

            $decoded = $this->decodeNestedJson($response->json());
            if (empty($decoded)) {
                return null;
            }

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
                'IDAsesorVenta' => $dto->advisorId,
                'IDAsesorAgr' => $dto->advisorId,
                'SlpCode' => $dto->advisorId,
                'slpCode' => $dto->advisorId,
                'AdvisorId' => $dto->advisorId,
                'advisorId' => $dto->advisorId,
                'Asesor' => $dto->advisorName,
                'AdvisorName' => $dto->advisorName,
                'advisorName' => $dto->advisorName,
                'SlpName' => $dto->advisorName,
                'slpName' => $dto->advisorName,

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
        try {
            $response = $this->client()->post("/planes-cliente/{$planCode}/cancelar", [
                'comentario' => $comment,
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
            $response = $this->client()->post("/planes-cliente/{$dto->planCode}/seguimiento", [
                'etapas' => $dto->records,
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
        try {
            $response = $this->client()->post("/planes-cliente/{$dto->planCode}/guardar-resultados", [
                'U_Rendimiento' => $dto->actualYield,
                'U_FechaCosecha' => $dto->harvestDate,
                'U_Comentarios' => $dto->technicalConclusions,
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
            Log::error('[HttpFertilizationPlanRepository] captureResults error', ['error' => $e->getMessage()]);

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
                return $this->decodeNestedJson($response->json());
            }

            return [];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getProposalPdfData error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getResultsPdfData(string $planCode): array
    {
        try {
            $response = $this->client()->get('/plan-resultados/datos-pdf', [
                'planCode' => $planCode,
            ]);

            if ($response->successful()) {
                return $this->decodeNestedJson($response->json());
            }

            return [];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] getResultsPdfData error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function savePlanPdf(string $planCode, int $version, string $status, string $base64File): array
    {
        try {
            $response = $this->client()->post('/plan-fertilizacion/guardar-pdf', [
                'planCode' => $planCode,
                'version' => $version,
                'status' => $status,
                'archivoBase64' => $base64File,
            ]);

            return [
                'success' => $response->successful(),
                'message' => $response->json('mensaje') ?? 'PDF guardado.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] savePlanPdf error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function saveResultsPdf(string $planCode, int $version, string $status, string $base64File): array
    {
        try {
            $response = $this->client()->post('/plan-resultados/guardar-pdf', [
                'planCode' => $planCode,
                'version' => $version,
                'status' => $status,
                'archivoBase64' => $base64File,
            ]);

            return [
                'success' => $response->successful(),
                'message' => $response->json('mensaje') ?? 'PDF de resultados guardado.',
            ];
        } catch (Throwable $e) {
            Log::error('[HttpFertilizationPlanRepository] saveResultsPdf error', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

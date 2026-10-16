<?php

declare(strict_types=1);

namespace App\UI\Livewire\Wizard;

use App\Application\UseCases\Plans\CreatePlanUseCase;
use App\Application\UseCases\Queries\GetCropRecommendationUseCase;
use App\Application\UseCases\WhatsApp\SendPlanWhatsAppUseCase;
use App\Domain\Catalogs\Repositories\CatalogsRepositoryInterface;
use App\Domain\FertilizationPlan\DTOs\CreatePlanDTO;
use App\Domain\FertilizationPlan\Repositories\FertilizationPlanRepositoryInterface;
use App\Domain\WhatsApp\DTOs\WhatsAppMessageDTO;
use App\Infrastructure\Pdf\DomPdfService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PlanWizard extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    // Step 1: General Data & GPS
    #[Rule('required', message: 'El cliente es obligatorio.')]
    public string $customerCode = '';

    public string $customerName = '';

    #[Rule('required', message: 'El asesor técnico es obligatorio.')]
    public string $advisorId = '';

    public string $advisorName = '';

    public string $advisorPhone = '';

    public string $customerPhone = '';

    #[Url]
    public ?string $phone = null;

    public bool $isSharingWhatsApp = false;

    public ?string $whatsAppSuccess = null;

    public ?string $whatsAppError = null;

    #[Rule('required', message: 'El nombre de la parcela es obligatorio.')]
    public string $parcela = '';

    #[Rule('required|numeric|min:0.1', message: 'La superficie es obligatoria y debe ser mayor a 0.')]
    public ?float $hectares = null;

    #[Rule('required', message: 'El tipo de suelo es obligatorio.')]
    public string $soilType = '';

    #[Rule('required', message: 'El análisis de suelo es obligatorio.')]
    public string $analisisSuelo = 'NO';

    public string $zone = '';

    public string $territory = '';

    public string $season = '2025-2026';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public string $coordenadasText = 'Sin coordenadas marcadas';

    public string $geoSearchQuery = '';

    public array $geoSearchResults = [];

    public bool $isSearchingGeo = false;

    // Step 2: Crops Data
    public ?string $previousCrop = null;

    public ?float $previousCropYield = null;

    public ?float $previousCropMaxYield = null;

    #[Rule('required', message: 'El cultivo actual es obligatorio.')]
    public ?string $currentCropCode = null;

    public ?string $currentCropName = null;

    public string $seedVariety = '';

    public ?string $estimatedSowingDate = null;

    public ?float $targetYield = null;

    public ?float $currentCropTargetYield = null;

    // Step 3: Stages & Products
    public array $stages = [];

    // Catalogs
    public array $zones = [];

    public array $territories = [];

    public array $soilTypes = [];

    public array $cropTypes = [];

    public array $customerCrops = [];

    public array $availableProducts = [];

    public array $applicationMethods = [];

    // Customer search dropdown state
    public string $customerSearchText = '';

    public array $customerSearchResults = [];

    public bool $isSearchingCustomer = false;

    // Advisor search dropdown state
    public string $advisorSearchText = '';

    public array $advisorSearchResults = [];

    public bool $isSearchingAdvisor = false;

    // Estados reactivos para modales custom Tailwind
    public bool $showSuccessModal = false;

    public string $successMessage = '';

    public string|int|null $savedPlanCode = null;

    public ?string $savedPlanMessage = null;

    public ?string $mapImageBase64 = null;

    public bool $showErrorModal = false;

    public string $errorMessage = '';

    public bool $isLoaded = false;

    public bool $isVersioning = false;

    public ?string $basePlanCode = null;

    public int $baseVersion = 1;

    public int $newVersion = 2;

    public function setMapImage(string $base64): void
    {
        $this->mapImageBase64 = $base64;
    }

    public function mount(?string $planCode = null): void
    {
        $this->estimatedSowingDate = null;
        if (! empty($planCode)) {
            $this->isVersioning = true;
            $this->basePlanCode = $planCode;
            $this->currentStep = 3;
        }
        if (! empty($this->phone)) {
            $this->customerPhone = $this->phone;
        }
    }

    public function loadInitialData(
        CatalogsRepositoryInterface $catalogs,
        ?FertilizationPlanRepositoryInterface $planRepo = null
    ): void {
        $this->zones = $catalogs->getZones();
        $this->soilTypes = $catalogs->getSoilTypes();
        $this->cropTypes = $catalogs->getCropTypes();
        $this->availableProducts = $catalogs->getProducts();
        $this->applicationMethods = $catalogs->getApplicationMethods();
        $this->isLoaded = true;

        if ($this->isVersioning && ! empty($this->basePlanCode)) {
            $repo = $planRepo ?? app(FertilizationPlanRepositoryInterface::class);
            $this->loadPlanForVersioning($this->basePlanCode, $repo, $catalogs);
        }
    }

    public function loadPlanForVersioning(
        string $planCode,
        FertilizationPlanRepositoryInterface $planRepo,
        CatalogsRepositoryInterface $catalogs
    ): void {
        $planData = $planRepo->getPlanForVersioning($planCode);

        if (empty($planData) || empty($planData['maestro'])) {
            $entity = $planRepo->getPlanByCode($planCode);
            if ($entity) {
                $this->customerCode = $entity->customerCode;
                $this->customerName = $entity->customerName;
                $this->parcela = $entity->parcela;
                $this->advisorId = $entity->advisorId;
                $this->advisorName = $entity->advisorName;
                $this->zone = $entity->zone;
                $this->territory = $entity->territory;
                $this->hectares = $entity->hectares;
                $this->soilType = $entity->soilType;
                $this->analisisSuelo = $entity->hasSoilAnalysis ? 'SI' : 'NO';
                $this->latitude = $entity->latitude;
                $this->longitude = $entity->longitude;
                $this->previousCrop = $entity->previousCrop;
                $this->previousCropYield = $entity->previousCropYield;
                $this->previousCropMaxYield = $entity->previousCropMaxYield;
                $this->currentCropCode = $entity->currentCropCode;
                $this->currentCropName = $entity->currentCropName;
                $this->estimatedSowingDate = $entity->estimatedSowingDate;
                $this->seedVariety = $entity->seedVariety;
                $this->targetYield = $entity->targetYield;
                $this->baseVersion = $entity->version;
                $this->newVersion = $this->baseVersion + 1;
                $this->stages = array_map(function ($s) {
                    return [
                        'stageId' => $s->stageId,
                        'stageName' => $s->stageName,
                        'applicationMethod' => $s->applicationMethod ?? 'Tradicional',
                        'applicationDate' => null,
                        'observations' => '',
                        'products' => array_map(function ($p) {
                            return [
                                'itemCode' => $p->itemCode,
                                'itemName' => $p->itemName,
                                'dosePerHectare' => $p->dosePerHectare,
                                'unitOfMeasure' => $p->unitOfMeasure,
                                'unitPrice' => $p->unitPrice,
                                'activeIngredient' => $p->activeIngredient,
                            ];
                        }, $s->products),
                    ];
                }, $entity->stages);
                $this->currentStep = 3;
            }

            return;
        }

        $m = $planData['maestro'] ?? [];
        $this->customerCode = (string) ($m['CardCode'] ?? '');
        $this->customerName = (string) ($m['CardName'] ?? '');
        if (! empty($m['CellularCliente'])) {
            $this->customerPhone = (string) $m['CellularCliente'];
        }
        $this->parcela = (string) ($m['Parcela'] ?? 'Parcela 1');
        $this->zone = (string) ($m['U_Zona'] ?? $m['Zona'] ?? '');
        $this->territory = (string) ($m['U_Territorio'] ?? $m['Territorio'] ?? '');
        $this->hectares = (float) ($m['Superficie'] ?? $m['U_Superficie'] ?? 1.0);
        $this->soilType = (string) ($m['CodeTipoSuelo'] ?? $m['U_Code_Tipo_Suelo'] ?? '');
        $this->analisisSuelo = (string) ($m['AnalisisSuelo'] ?? $m['U_Analisis_Suelo'] ?? 'NO');
        $this->advisorId = (string) ($m['IDAsesorAgr'] ?? $m['SlpCode'] ?? '');
        $this->advisorName = (string) ($m['U_Asesor'] ?? $m['SlpName'] ?? '');
        $this->advisorPhone = (string) ($m['Cellular'] ?? '');
        $this->previousCrop = (string) ($m['CodeCultAnterior'] ?? '');
        $this->previousCropYield = isset($m['RendimientoReal']) ? (float) $m['RendimientoReal'] : null;
        $this->previousCropMaxYield = isset($m['RendimientoMax']) ? (float) $m['RendimientoMax'] : (isset($m['U_Rendimiento_Max']) ? (float) $m['U_Rendimiento_Max'] : null);
        $this->currentCropCode = (string) ($m['CodeCultivo'] ?? $m['U_Code_Cultivo'] ?? '');
        $this->currentCropName = (string) ($planData['cultivoNombre'] ?? $m['nombrecultivo'] ?? 'Cultivo');
        $this->estimatedSowingDate = isset($m['FechaSiembra']) ? explode('T', (string) $m['FechaSiembra'])[0] : null;
        $this->seedVariety = (string) ($m['VariedadSemilla'] ?? '');
        $this->targetYield = isset($m['ObjetivoProd']) ? (float) $m['ObjetivoProd'] : null;
        $rawVersion = preg_replace('/\D/', '', (string) ($m['VersionActual'] ?? $m['U_Version'] ?? '1'));
        $this->baseVersion = (int) ($rawVersion ?: 1);
        $this->newVersion = $this->baseVersion + 1;

        if (! empty($this->customerCode) && (empty($this->customerName) || empty($this->customerPhone))) {
            try {
                $custs = $catalogs->searchCustomers($this->customerCode);
                $matchedCust = collect($custs)->first(fn ($c) => ($c['CardCode'] ?? '') === $this->customerCode);
                if ($matchedCust) {
                    if (empty($this->customerName)) {
                        $this->customerName = (string) ($matchedCust['CardName'] ?? '');
                    }
                    if (empty($this->customerPhone)) {
                        $this->customerPhone = (string) ($matchedCust['Cellular'] ?? '');
                    }
                    if (empty($this->zone) && ! empty($matchedCust['Zone'])) {
                        $this->zone = (string) $matchedCust['Zone'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[PlanWizard] Error al buscar cliente para versionado', ['error' => $e->getMessage()]);
            }
        }

        if (empty($this->advisorName) && ! empty($this->advisorId)) {
            try {
                $advs = $catalogs->searchAdvisors($this->advisorId);
                $matchedAdv = collect($advs)->first(fn ($a) => (string) ($a['SlpCode'] ?? $a['id'] ?? '') === (string) $this->advisorId);
                if ($matchedAdv) {
                    $this->advisorName = (string) ($matchedAdv['SlpName'] ?? $matchedAdv['name'] ?? '');
                    if (empty($this->advisorPhone) && ! empty($matchedAdv['Cellular'])) {
                        $this->advisorPhone = (string) $matchedAdv['Cellular'];
                    }
                    if (empty($this->zone) && ! empty($matchedAdv['Zona'])) {
                        $this->zone = (string) $matchedAdv['Zona'];
                    }
                    if (empty($this->territory) && ! empty($matchedAdv['Territorio'])) {
                        $this->territory = (string) $matchedAdv['Territorio'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[PlanWizard] Error al buscar asesor por ID para versionado', ['error' => $e->getMessage()]);
            }
        }

        $authUser = session('authenticated_user');
        if (empty($this->advisorName) && $authUser) {
            $this->advisorName = $authUser->name ?? '';
            if (empty($this->advisorPhone) && ! empty($authUser->phone)) {
                $this->advisorPhone = (string) $authUser->phone;
            }
        }

        if (! empty($this->currentCropCode) && ($this->currentCropName === 'Cultivo' || empty($this->currentCropName))) {
            $matchedCrop = collect($this->cropTypes)->first(fn ($c) => ($c['Code'] ?? '') === $this->currentCropCode);
            if ($matchedCrop) {
                $this->currentCropName = (string) ($matchedCrop['Name'] ?? 'Cultivo');
            }
        }

        $coordsStr = $m['Coordenadas'] ?? $m['U_Coordenadas'] ?? null;
        if ($coordsStr && str_contains($coordsStr, ',')) {
            [$lat, $lng] = explode(',', $coordsStr);
            $this->latitude = (float) trim($lat);
            $this->longitude = (float) trim($lng);
            $this->coordenadasText = sprintf('Lat: %.5f, Lng: %.5f', $this->latitude, $this->longitude);
        }

        // Fusión de etapas: Recomendación de catálogo + Etapas guardadas
        $recEtapas = [];
        if (! empty($this->currentCropCode)) {
            try {
                $recResp = $catalogs->getCropRecommendation($this->currentCropCode, $this->territory ?: null);
                $recEtapas = $recResp['etapas'] ?? [];
            } catch (\Throwable $e) {
                Log::warning('[PlanWizard] Error al obtener recomendación para fusionar en versionado', ['error' => $e->getMessage()]);
            }
        }

        $rawEtapas = $planData['etapas'] ?? [];
        $baseLoop = ! empty($recEtapas) ? $recEtapas : array_map(fn ($e) => [
            'idEtapa' => $e['codeEtapa'] ?? 0,
            'nombreEtapa' => $e['nameEtapa'] ?? '',
        ], $rawEtapas);

        $this->stages = [];
        $processedStageIds = [];

        foreach ($baseLoop as $tpl) {
            $stageId = (int) ($tpl['idEtapa'] ?? $tpl['codeEtapa'] ?? 0);
            $stageName = (string) ($tpl['nombreEtapa'] ?? $tpl['nameEtapa'] ?? "Etapa {$stageId}");
            $processedStageIds[] = $stageId;

            $saved = collect($rawEtapas)->first(fn ($g) => (int) ($g['codeEtapa'] ?? 0) === $stageId);

            if ($saved) {
                $prodsRaw = [];
                if (! empty($saved['listaProductos'])) {
                    $prodsRaw = is_string($saved['listaProductos']) ? (json_decode($saved['listaProductos'], true) ?: []) : $saved['listaProductos'];
                }

                $mappedProds = [];
                foreach ($prodsRaw as $pr) {
                    $pId = (string) ($pr['id'] ?? $pr['itemCode'] ?? $pr['idProducto'] ?? '');
                    $pDose = (float) ($pr['dosis'] ?? $pr['dosePerHectare'] ?? 0.0);
                    $foundCat = collect($this->availableProducts)->first(fn ($x) => ($x['ItemCode'] ?? $x['itemCode'] ?? '') === $pId);
                    $mappedProds[] = [
                        'itemCode' => $pId,
                        'itemName' => $foundCat['ItemName'] ?? $foundCat['name'] ?? ($pr['nombre'] ?? $pr['nombreProducto'] ?? $pId),
                        'dosePerHectare' => $pDose,
                        'unitOfMeasure' => $foundCat['SalUnitMsr'] ?? 'Kg/Ha',
                        'unitPrice' => (float) ($foundCat['Price'] ?? 0.0),
                        'activeIngredient' => $foundCat['U_Det_IngActivo'] ?? ($pr['ingredienteActivo'] ?? null),
                    ];
                }

                $appMethodVal = 'Tradicional';
                if (! empty($saved['idAplicacion'])) {
                    $idApp = (string) $saved['idAplicacion'];
                    $foundMethod = collect($this->applicationMethods)->first(fn ($am) => ($am['Code'] ?? '') === $idApp);
                    $appMethodVal = $foundMethod['Name'] ?? $idApp;
                }

                $this->stages[] = [
                    'stageId' => $stageId,
                    'stageName' => $stageName,
                    'applicationMethod' => $appMethodVal,
                    'applicationDate' => ! empty($saved['fechaAplicacion']) ? explode('T', (string) $saved['fechaAplicacion'])[0] : null,
                    'observations' => (string) ($saved['comentario'] ?? ''),
                    'products' => $mappedProds,
                ];
            } else {
                $this->stages[] = [
                    'stageId' => $stageId,
                    'stageName' => $stageName,
                    'applicationMethod' => 'Tradicional',
                    'applicationDate' => null,
                    'observations' => (string) ($tpl['comentario'] ?? ''),
                    'products' => [],
                ];
            }
        }

        foreach ($rawEtapas as $st) {
            $stId = (int) ($st['codeEtapa'] ?? 0);
            if (! in_array($stId, $processedStageIds, true)) {
                $prodsRaw = [];
                if (! empty($st['listaProductos'])) {
                    $prodsRaw = is_string($st['listaProductos']) ? (json_decode($st['listaProductos'], true) ?: []) : $st['listaProductos'];
                }

                $mappedProds = [];
                foreach ($prodsRaw as $pr) {
                    $pId = (string) ($pr['id'] ?? $pr['itemCode'] ?? $pr['idProducto'] ?? '');
                    $pDose = (float) ($pr['dosis'] ?? $pr['dosePerHectare'] ?? 0.0);
                    $foundCat = collect($this->availableProducts)->first(fn ($x) => ($x['ItemCode'] ?? $x['itemCode'] ?? '') === $pId);
                    $mappedProds[] = [
                        'itemCode' => $pId,
                        'itemName' => $foundCat['ItemName'] ?? $foundCat['name'] ?? ($pr['nombre'] ?? $pr['nombreProducto'] ?? $pId),
                        'dosePerHectare' => $pDose,
                        'unitOfMeasure' => $foundCat['SalUnitMsr'] ?? 'Kg/Ha',
                        'unitPrice' => (float) ($foundCat['Price'] ?? 0.0),
                        'activeIngredient' => $foundCat['U_Det_IngActivo'] ?? ($pr['ingredienteActivo'] ?? null),
                    ];
                }

                $appMethodVal = 'Tradicional';
                if (! empty($st['idAplicacion'])) {
                    $idApp = (string) $st['idAplicacion'];
                    $foundMethod = collect($this->applicationMethods)->first(fn ($am) => ($am['Code'] ?? '') === $idApp);
                    $appMethodVal = $foundMethod['Name'] ?? $idApp;
                }

                $this->stages[] = [
                    'stageId' => $stId,
                    'stageName' => (string) ($st['nameEtapa'] ?? "Etapa {$stId}"),
                    'applicationMethod' => $appMethodVal,
                    'applicationDate' => ! empty($st['fechaAplicacion']) ? explode('T', (string) $st['fechaAplicacion'])[0] : null,
                    'observations' => (string) ($st['comentario'] ?? ''),
                    'products' => $mappedProds,
                ];
            }
        }

        usort($this->stages, fn ($a, $b) => ($a['stageId'] ?? 0) <=> ($b['stageId'] ?? 0));

        $this->currentStep = 3;
    }

    public function updatedZone(string $value, CatalogsRepositoryInterface $catalogs): void
    {
        $this->territory = '';
        $this->territories = ! empty($value) ? $catalogs->getTerritoriesByZone($value) : [];
    }

    public function updatedCustomerSearchText(string $value, CatalogsRepositoryInterface $catalogs): void
    {
        $val = trim($value);
        if (strlen($val) < 1) {
            $this->customerSearchResults = [];
            $this->customerCode = '';
            $this->customerName = '';

            return;
        }

        $this->isSearchingCustomer = true;
        $this->customerSearchResults = $catalogs->searchCustomers($val, $this->zone ?: null);
        $this->isSearchingCustomer = false;
    }

    public function searchCustomers(CatalogsRepositoryInterface $catalogs): void
    {
        $val = trim($this->customerSearchText);
        if (strlen($val) < 1) {
            $this->customerSearchResults = [];

            return;
        }

        $this->isSearchingCustomer = true;
        $this->customerSearchResults = $catalogs->searchCustomers($val, $this->zone ?: null);
        $this->isSearchingCustomer = false;
    }

    public function selectCustomer(string $cardCode, string $cardName, CatalogsRepositoryInterface $catalogs): void
    {
        $this->customerCode = $cardCode;
        $this->customerName = $cardName;
        $this->customerSearchText = "{$cardName}";
        $this->resetErrorBag(['customerCode', 'customerSearchText']);

        foreach ($this->customerSearchResults as $c) {
            if (($c['CardCode'] ?? '') === $cardCode) {
                if (! empty($c['Zone']) && empty($this->zone)) {
                    $this->zone = $c['Zone'];
                }
                if (! empty($c['Cellular'])) {
                    $this->customerPhone = (string) $c['Cellular'];
                }
                break;
            }
        }

        $this->customerSearchResults = [];

        // Load customer crops
        $this->customerCrops = $catalogs->getCustomerCrops($cardCode);
    }

    public function updatedAdvisorSearchText(string $value, CatalogsRepositoryInterface $catalogs): void
    {
        $val = trim($value);
        if (strlen($val) < 1) {
            $this->advisorSearchResults = [];
            $this->advisorId = '';
            $this->advisorName = '';

            return;
        }

        $this->isSearchingAdvisor = true;
        $this->advisorSearchResults = $catalogs->searchAdvisors($val);
        $this->isSearchingAdvisor = false;
    }

    public function searchAdvisors(CatalogsRepositoryInterface $catalogs): void
    {
        $val = trim($this->advisorSearchText);
        if (strlen($val) < 1) {
            $this->advisorSearchResults = [];

            return;
        }

        $this->isSearchingAdvisor = true;
        $this->advisorSearchResults = $catalogs->searchAdvisors($val);
        $this->isSearchingAdvisor = false;
    }

    public function selectAdvisor(string $advisorId, string $advisorName, ?string $phone = null): void
    {
        $this->advisorId = $advisorId;
        $this->advisorName = $advisorName;
        $this->advisorPhone = (string) ($phone ?? '');
        $this->advisorSearchText = "{$advisorName}";
        $this->resetErrorBag(['advisorId', 'advisorSearchText']);
        $this->advisorSearchResults = [];
    }

    public function updatedCurrentCropCode(string $value, GetCropRecommendationUseCase $recommendationUseCase): void
    {
        $this->resetErrorBag('currentCropCode');
        $this->estimatedSowingDate = null;
        $this->seedVariety = '';

        foreach ($this->customerCrops as $c) {
            if (($c['cultivoId'] ?? $c['code'] ?? '') === $value) {
                $this->currentCropName = $c['nombreCultivo'] ?? $c['name'] ?? '';
                if (! empty($c['fechaSiembra'])) {
                    $this->estimatedSowingDate = substr($c['fechaSiembra'], 0, 10);
                }
                if (! empty($c['variedad'])) {
                    $this->seedVariety = $c['variedad'];
                }
                break;
            }
        }

        // Auto-fetch standard crop recommendation
        $recommendation = $recommendationUseCase->execute($value, $this->territory ?: null);
        if (! empty($recommendation['etapas'])) {
            $this->stages = array_map(function ($stage) {
                $stage['applicationDate'] = ! empty($stage['applicationDate'])
                    ? substr($stage['applicationDate'], 0, 10)
                    : (! empty($stage['fechaAplicacion']) ? substr($stage['fechaAplicacion'], 0, 10) : null);
                $stage['applicationMethod'] = $stage['applicationMethod'] ?? $stage['metodo'] ?? 'Tradicional';

                return $stage;
            }, $recommendation['etapas']);
        }
    }

    public function setCoordinates(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->coordenadasText = sprintf('Lat: %.6f, Lng: %.6f', $lat, $lng);
        $this->resetErrorBag(['latitude', 'longitude']);
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'customerCode' => 'required',
                'advisorId' => 'required',
                'parcela' => 'required',
                'hectares' => 'required|numeric|min:0.1',
                'soilType' => 'required',
                'analisisSuelo' => 'required',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ], [
                'customerCode.required' => 'El cliente es obligatorio.',
                'advisorId.required' => 'El asesor técnico es obligatorio.',
                'parcela.required' => 'El nombre de la parcela es obligatorio.',
                'hectares.required' => 'La superficie es obligatoria.',
                'soilType.required' => 'El tipo de suelo es obligatorio.',
                'analisisSuelo.required' => 'El análisis de suelo es obligatorio.',
                'latitude.required' => 'Selecciona un punto en el mapa para marcar la ubicación de la parcela.',
                'longitude.required' => 'Selecciona un punto en el mapa para marcar la ubicación de la parcela.',
            ]);
            $this->currentStep = 2;
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'previousCrop' => 'required',
                'previousCropYield' => 'required|numeric|min:0.01',
                'previousCropMaxYield' => 'required|numeric|min:0.01',
                'currentCropCode' => 'required',
                'seedVariety' => 'required|string|min:1',
                'estimatedSowingDate' => 'required|date',
                'targetYield' => 'required|numeric|min:0.01',
            ], [
                'previousCrop.required' => 'El cultivo anterior es obligatorio.',
                'previousCropYield.required' => 'El rendimiento real es obligatorio.',
                'previousCropYield.numeric' => 'El rendimiento real debe ser numérico.',
                'previousCropYield.min' => 'El rendimiento real debe ser mayor a 0.',
                'previousCropMaxYield.required' => 'El rendimiento máximo de referencia es obligatorio.',
                'previousCropMaxYield.numeric' => 'El rendimiento máximo debe ser numérico.',
                'previousCropMaxYield.min' => 'El rendimiento máximo debe ser mayor a 0.',
                'currentCropCode.required' => 'El cultivo actual es obligatorio.',
                'seedVariety.required' => 'La variedad de semilla es obligatoria.',
                'estimatedSowingDate.required' => 'La fecha de siembra es obligatoria.',
                'targetYield.required' => 'La meta de rendimiento es obligatoria.',
                'targetYield.numeric' => 'La meta de rendimiento debe ser numérica.',
                'targetYield.min' => 'La meta de rendimiento debe ser mayor a 0.',
            ]);
            $this->currentStep = 3;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function updated(string $propertyName): void
    {
        $this->resetErrorBag($propertyName);

        if (str_starts_with($propertyName, 'stages')) {
            $this->resetErrorBag('stages');
            $parts = explode('.', $propertyName);
            if (isset($parts[1])) {
                $stageIndex = (int) $parts[1];
                $this->resetErrorBag("stages.{$stageIndex}.products");
                $this->resetErrorBag("stages.{$stageIndex}.applicationDate");
                $this->resetErrorBag("stages.{$stageIndex}.applicationMethod");
                if (isset($parts[3])) {
                    $prodIndex = (int) $parts[3];
                    $this->resetErrorBag("stages.{$stageIndex}.products.{$prodIndex}.itemCode");
                    $this->resetErrorBag("stages.{$stageIndex}.products.{$prodIndex}.dosePerHectare");
                }
            }
        }
    }

    public function addStage(): void
    {
        $this->resetErrorBag('stages');
        $newIndex = count($this->stages) + 1;
        $this->stages[] = [
            'stageId' => $newIndex,
            'stageName' => "Etapa {$newIndex}",
            'orderIndex' => $newIndex,
            'applicationDate' => null,
            'applicationMethod' => 'Tradicional',
            'observations' => '',
            'products' => [],
        ];
    }

    public function removeStage(int $index): void
    {
        unset($this->stages[$index]);
        $this->stages = array_values($this->stages);
        $this->resetErrorBag('stages');
    }

    public function addProductToStage(int $stageIndex): void
    {
        $this->resetErrorBag("stages.{$stageIndex}.products");
        $this->resetErrorBag('stages');

        $this->stages[$stageIndex]['products'][] = [
            'itemCode' => '',
            'itemName' => '',
            'dosePerHectare' => null,
            'unitOfMeasure' => 'Kg/Ha',
            'unitPrice' => 0.0,
            'activeIngredient' => '',
            'applicationMethod' => $this->stages[$stageIndex]['applicationMethod'] ?? 'Fertirriego',
            'isImpulsoraProduct' => true,
        ];
    }

    public function removeProductFromStage(int $stageIndex, int $productIndex): void
    {
        unset($this->stages[$stageIndex]['products'][$productIndex]);
        $this->stages[$stageIndex]['products'] = array_values($this->stages[$stageIndex]['products']);

        if (! empty($this->stages[$stageIndex]['products'])) {
            $this->resetErrorBag("stages.{$stageIndex}.products");
        }
    }

    public function savePlan(CreatePlanUseCase $createPlanUseCase): void
    {
        try {
            if ($this->isVersioning && ! empty($this->basePlanCode)) {
                // Prune empty stages without products and without dates/methods (matching React shouldIncludeStage)
                $this->stages = array_values(array_filter($this->stages, function ($st) {
                    $hasProducts = ! empty($st['products']);
                    $hasDate = ! empty($st['applicationDate']);
                    $hasMethod = ! empty($st['applicationMethod']) && $st['applicationMethod'] !== 'Tradicional';

                    return $hasProducts || $hasDate || $hasMethod;
                }));

                $this->validate([
                    'stages' => 'required|array|min:1',
                    'stages.*.applicationDate' => 'required|date',
                    'stages.*.applicationMethod' => 'required|string',
                    'stages.*.observations' => 'nullable|string|max:253',
                    'stages.*.products' => 'required|array|min:1',
                    'stages.*.products.*.itemCode' => 'required|string|min:1',
                    'stages.*.products.*.dosePerHectare' => 'required|numeric|min:0.0001',
                ], [
                    'stages.required' => 'Debes definir al menos una etapa fenológica en la propuesta.',
                    'stages.min' => 'Debes definir al menos una etapa fenológica en la propuesta.',
                    'stages.*.applicationDate.required' => 'La fecha de aplicación es obligatoria.',
                    'stages.*.applicationDate.date' => 'La fecha de aplicación debe ser una fecha válida.',
                    'stages.*.applicationMethod.required' => 'El método de aplicación es obligatorio.',
                    'stages.*.observations.max' => 'Los comentarios no pueden exceder los 253 caracteres.',
                    'stages.*.products.required' => 'Cada etapa debe contener al menos un producto.',
                    'stages.*.products.min' => 'Cada etapa debe contener al menos un producto.',
                    'stages.*.products.*.itemCode.required' => 'Debes seleccionar un producto en cada fila.',
                    'stages.*.products.*.dosePerHectare.required' => 'La dosis es obligatoria.',
                    'stages.*.products.*.dosePerHectare.numeric' => 'La dosis debe ser un valor numérico.',
                    'stages.*.products.*.dosePerHectare.min' => 'La dosis debe ser mayor a 0.',
                ]);
            } else {
                $this->validate([
                    // Step 1
                    'customerCode' => 'required',
                    'advisorId' => 'required',
                    'parcela' => 'required',
                    'hectares' => 'required|numeric|min:0.1',
                    'soilType' => 'required',
                    'analisisSuelo' => 'required',
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    // Step 2
                    'previousCrop' => 'required',
                    'previousCropYield' => 'required|numeric|min:0.01',
                    'previousCropMaxYield' => 'required|numeric|min:0.01',
                    'currentCropCode' => 'required',
                    'seedVariety' => 'required|string|min:1',
                    'estimatedSowingDate' => 'required|date',
                    'targetYield' => 'required|numeric|min:0.01',
                    // Step 3
                    'stages' => 'required|array|min:1',
                    'stages.*.applicationDate' => 'required|date',
                    'stages.*.applicationMethod' => 'required|string',
                    'stages.*.observations' => 'nullable|string|max:253',
                    'stages.*.products' => 'required|array|min:1',
                    'stages.*.products.*.itemCode' => 'required|string|min:1',
                    'stages.*.products.*.dosePerHectare' => 'required|numeric|min:0.0001',
                ], [
                    'customerCode.required' => 'El cliente es obligatorio.',
                    'advisorId.required' => 'El asesor técnico es obligatorio.',
                    'parcela.required' => 'El nombre de la parcela es obligatorio.',
                    'hectares.required' => 'La superficie es obligatoria.',
                    'soilType.required' => 'El tipo de suelo es obligatorio.',
                    'analisisSuelo.required' => 'El análisis de suelo es obligatorio.',
                    'latitude.required' => 'Selecciona un punto en el mapa para marcar la ubicación de la parcela.',
                    'longitude.required' => 'Selecciona un punto en el mapa para marcar la ubicación de la parcela.',
                    'previousCrop.required' => 'El cultivo anterior es obligatorio.',
                    'previousCropYield.required' => 'El rendimiento real es obligatorio.',
                    'previousCropYield.numeric' => 'El rendimiento real debe ser numérico.',
                    'previousCropYield.min' => 'El rendimiento real debe ser mayor a 0.',
                    'previousCropMaxYield.required' => 'El rendimiento máximo de referencia es obligatorio.',
                    'previousCropMaxYield.numeric' => 'El rendimiento máximo debe ser numérico.',
                    'previousCropMaxYield.min' => 'El rendimiento máximo debe ser mayor a 0.',
                    'currentCropCode.required' => 'El cultivo actual es obligatorio.',
                    'seedVariety.required' => 'La variedad de semilla es obligatoria.',
                    'estimatedSowingDate.required' => 'La fecha de siembra es obligatoria.',
                    'targetYield.required' => 'La meta de rendimiento es obligatoria.',
                    'targetYield.numeric' => 'La meta de rendimiento debe ser numérica.',
                    'targetYield.min' => 'La meta de rendimiento debe ser mayor a 0.',
                    'stages.required' => 'Debes definir al menos una etapa fenológica en la propuesta.',
                    'stages.min' => 'Debes definir al menos una etapa fenológica en la propuesta.',
                    'stages.*.applicationDate.required' => 'La fecha de aplicación es obligatoria.',
                    'stages.*.applicationDate.date' => 'La fecha de aplicación debe ser una fecha válida.',
                    'stages.*.applicationMethod.required' => 'El método de aplicación es obligatorio.',
                    'stages.*.observations.max' => 'Los comentarios no pueden exceder los 253 caracteres.',
                    'stages.*.products.required' => 'Cada etapa debe contener al menos un producto.',
                    'stages.*.products.min' => 'Cada etapa debe contener al menos un producto.',
                    'stages.*.products.*.itemCode.required' => 'Debes seleccionar un producto en cada fila.',
                    'stages.*.products.*.dosePerHectare.required' => 'La dosis es obligatoria.',
                    'stages.*.products.*.dosePerHectare.numeric' => 'La dosis debe ser un valor numérico.',
                    'stages.*.products.*.dosePerHectare.min' => 'La dosis debe ser mayor a 0.',
                ]);
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            Log::warning('[PlanWizard] Validación fallida al guardar plan', [
                'errors' => $errors->toArray(),
            ]);

            if (! $this->isVersioning) {
                if ($errors->hasAny(['customerCode', 'advisorId', 'parcela', 'hectares', 'soilType', 'analisisSuelo', 'latitude', 'longitude'])) {
                    $this->currentStep = 1;
                } elseif ($errors->hasAny(['previousCrop', 'previousCropYield', 'previousCropMaxYield', 'currentCropCode', 'seedVariety', 'estimatedSowingDate', 'targetYield'])) {
                    $this->currentStep = 2;
                }
            }

            throw $e;
        }

        try {
            if ($this->isVersioning && ! empty($this->basePlanCode)) {
                $maestroData = [
                    'Code' => $this->basePlanCode,
                    'CardCode' => $this->customerCode,
                    'Name' => "Plan {$this->parcela} - {$this->customerCode}",
                    'Parcela' => $this->parcela,
                    'Superficie' => (float) $this->hectares,
                    'CodeTipoSuelo' => $this->soilType,
                    'AnalisisSuelo' => strtoupper((string) $this->analisisSuelo) === 'SI' ? 'SI' : 'NO',
                    'Coordenadas' => ($this->latitude && $this->longitude) ? "{$this->latitude},{$this->longitude}" : null,
                    'CodeCultAnterior' => $this->previousCrop,
                    'RendimientoReal' => $this->previousCropYield ? (float) $this->previousCropYield : null,
                    'RendimientoMax' => $this->previousCropMaxYield ? (float) $this->previousCropMaxYield : null,
                    'CodeCultivo' => $this->currentCropCode,
                    'FechaSiembra' => $this->estimatedSowingDate,
                    'VariedadSemilla' => $this->seedVariety,
                    'ObjetivoProd' => ($this->targetYield ?? $this->currentCropTargetYield) ? (float) ($this->targetYield ?? $this->currentCropTargetYield) : null,
                    'IDAsesorAgr' => $this->advisorId,
                    'IDAsesorVenta' => $this->advisorId,
                ];

                $etapasArray = [];
                foreach ($this->stages as $st) {
                    $productsToSend = [];
                    foreach ($st['products'] ?? [] as $p) {
                        $d = (float) ($p['dosePerHectare'] ?? $p['dosis'] ?? 0);
                        $iCode = $p['itemCode'] ?? $p['id'] ?? null;
                        if (! empty($iCode) && $d > 0) {
                            $productsToSend[] = [
                                'id' => $iCode,
                                'dosis' => number_format($d, 4, '.', ''),
                            ];
                        }
                    }

                    $idApp = null;
                    if (! empty($st['applicationMethod'])) {
                        if (is_numeric($st['applicationMethod'])) {
                            $idApp = (int) $st['applicationMethod'];
                        } else {
                            $matchedMethod = collect($this->applicationMethods)->first(fn ($am) => ($am['Name'] ?? '') === $st['applicationMethod'] || ($am['Code'] ?? '') === $st['applicationMethod']);
                            if ($matchedMethod && isset($matchedMethod['Code']) && is_numeric($matchedMethod['Code'])) {
                                $idApp = (int) $matchedMethod['Code'];
                            }
                        }
                    }

                    $etapasArray[] = [
                        'codeEtapa' => (int) ($st['stageId'] ?? 0),
                        'nameEtapa' => (string) ($st['stageName'] ?? ''),
                        'listaProductos' => json_encode($productsToSend),
                        'fechaAplicacion' => $st['applicationDate'] ?? null,
                        'idAplicacion' => $idApp,
                        'comentario' => $st['observations'] ?? '',
                        'U_Code_PlanFert' => $this->basePlanCode,
                    ];
                }

                $versionResult = app(FertilizationPlanRepositoryInterface::class)->versionPlanFull(
                    $this->basePlanCode,
                    $maestroData,
                    $etapasArray
                );

                if (empty($versionResult['success'])) {
                    $this->errorMessage = $versionResult['message'] ?? 'Error al versionar plan.';
                    $this->showErrorModal = true;

                    return;
                }

                $this->newVersion = (int) ($versionResult['version'] ?? ($this->baseVersion + 1));
                $this->savedPlanCode = $this->basePlanCode;
                $this->savedPlanMessage = "{$this->basePlanCode} - Plan {$this->parcela} - {$this->customerCode}";
                $this->successMessage = "¡Se ha creado la versión V{$this->newVersion} del plan {$this->basePlanCode} exitosamente!";

                try {
                    $planData = $this->getPlanDataForPdf();
                    $planData['version'] = $this->newVersion;
                    $pdfOutput = app(DomPdfService::class)->generateProposalPdf($planData);
                    app(FertilizationPlanRepositoryInterface::class)->savePlanPdf(
                        planCode: (string) $this->basePlanCode,
                        version: $this->newVersion,
                        status: 'Activo',
                        base64File: base64_encode($pdfOutput)
                    );
                } catch (\Throwable $pdfError) {
                    Log::warning('[PlanWizard] Error al guardar PDF de versión', ['error' => $pdfError->getMessage()]);
                }

                $this->showSuccessModal = true;

                return;
            }

            $targetYieldVal = $this->targetYield ?? $this->currentCropTargetYield;
            $hasSoilAnalysis = strtoupper((string) $this->analisisSuelo) === 'SI';

            $authUser = session('authenticated_user');
            $salesAdvisorId = (string) ($authUser?->id ?? '');
            $salesAdvisorName = (string) ($authUser?->name ?? 'Asesor Comercial');

            $dto = new CreatePlanDTO(
                customerCode: $this->customerCode,
                customerName: $this->customerName,
                advisorId: $this->advisorId,
                advisorName: $this->advisorName,
                parcela: $this->parcela,
                zone: $this->zone,
                territory: $this->territory,
                season: $this->season,
                hectares: (float) ($this->hectares ?? 1.0),
                soilType: $this->soilType,
                latitude: $this->latitude,
                longitude: $this->longitude,
                hasSoilAnalysis: $hasSoilAnalysis,
                hasTestigo: false,
                previousCrop: $this->previousCrop,
                previousCropYield: $this->previousCropYield ? (float) $this->previousCropYield : null,
                previousCropMaxYield: $this->previousCropMaxYield ? (float) $this->previousCropMaxYield : null,
                currentCropCode: $this->currentCropCode,
                currentCropName: $this->currentCropName,
                seedVariety: $this->seedVariety,
                estimatedSowingDate: $this->estimatedSowingDate,
                targetYield: $targetYieldVal ? (float) $targetYieldVal : null,
                stages: $this->stages,
                salesAdvisorId: $salesAdvisorId,
                salesAdvisorName: $salesAdvisorName,
            );

            Log::info('[PlanWizard] Ejecutando CreatePlanUseCase', [
                'customerCode' => $this->customerCode,
                'parcela' => $this->parcela,
                'stages_count' => count($this->stages),
            ]);

            $result = $createPlanUseCase->execute($dto);

            Log::info('[PlanWizard] Resultado retornado por CreatePlanUseCase', ['result' => $result]);

            $hasError = (isset($result['success']) && ! $result['success'])
                || (! empty($result['estado']) && $result['estado'] === 1);

            if ($hasError) {
                $errorMsg = $result['message'] ?? $result['mensaje'] ?? 'Error al registrar el plan de fertilización.';
                Log::error('[PlanWizard] Error retornado por endpoint al registrar plan', ['error' => $errorMsg]);

                $this->errorMessage = $errorMsg;
                $this->showErrorModal = true;

                return;
            }

            $planCode = $result['planCode'] ?? $result['codigo'] ?? null;
            $planMessage = $result['message'] ?? $result['mensaje'] ?? null;
            $successMsg = $planMessage ?? '¡Plan de fertilización registrado exitosamente!';

            $this->savedPlanCode = $planCode !== null ? (string) $planCode : null;
            $this->savedPlanMessage = $planMessage ?: ($this->savedPlanCode ? "{$this->savedPlanCode} - Plan {$this->parcela} - {$this->customerCode}" : null);
            $this->successMessage = 'El plan de fertilización ha sido registrado exitosamente en el sistema.';

            // Guardar copia del PDF de propuesta en BD SQL Server vía API
            if ($this->savedPlanCode) {
                try {
                    $planData = $this->getPlanDataForPdf();
                    $pdfOutput = app(DomPdfService::class)->generateProposalPdf($planData);
                    app(FertilizationPlanRepositoryInterface::class)->savePlanPdf(
                        planCode: (string) $this->savedPlanCode,
                        version: 1,
                        status: 'Activo',
                        base64File: base64_encode($pdfOutput)
                    );
                } catch (\Throwable $pdfError) {
                    Log::warning('[PlanWizard] No se pudo guardar la copia del PDF en la BD', ['error' => $pdfError->getMessage()]);
                }
            }

            $this->showSuccessModal = true;
        } catch (\Throwable $e) {
            Log::error('[PlanWizard] Excepción en endpoint al guardar plan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->errorMessage = $e->getMessage();
            $this->showErrorModal = true;
        }
    }

    public function getPlanDataForPdf(): array
    {
        $superficie = (float) ($this->hectares ?? 1.0);
        $coordStr = ($this->latitude && $this->longitude) ? "{$this->latitude},{$this->longitude}" : null;

        $catalogMap = [];
        if (! empty($this->availableProducts)) {
            foreach ($this->availableProducts as $ap) {
                $code = (string) ($ap['ItemCode'] ?? $ap['itemCode'] ?? '');
                if ($code !== '') {
                    $catalogMap[$code] = $ap['U_Det_IngActivo'] ?? $ap['activeIngredient'] ?? null;
                }
            }
        }

        $planMessage = $this->savedPlanMessage ?: ($this->savedPlanCode ? "{$this->savedPlanCode} - Plan {$this->parcela} - {$this->customerCode}" : "Plan {$this->parcela} - {$this->customerCode}");

        $advisorDisplay = $this->advisorName;
        if (str_contains($advisorDisplay, '@')) {
            $prefix = explode('@', $advisorDisplay)[0];
            $words = preg_split('/[._-]/', $prefix);
            $advisorDisplay = ucwords(implode(' ', array_filter($words ?: [$prefix])));
        }

        $salesAdvisorDisplay = (string) (session('authenticated_user')?->name ?? '');
        if (str_contains($salesAdvisorDisplay, '@')) {
            $prefix = explode('@', $salesAdvisorDisplay)[0];
            $words = preg_split('/[._-]/', $prefix);
            $salesAdvisorDisplay = ucwords(implode(' ', array_filter($words ?: [$prefix])));
        }
        if (empty($salesAdvisorDisplay)) {
            $salesAdvisorDisplay = 'Asesor Comercial';
        }

        return [
            // General
            'planCode' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'PlanCode' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'id' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'mensaje' => $planMessage,
            'version' => $this->isVersioning ? $this->newVersion : 1,
            'Version' => $this->isVersioning ? $this->newVersion : 1,
            'customerName' => $this->customerName,
            'productor' => $this->customerName,
            'CardName' => $this->customerName,
            'CardCode' => $this->customerCode,
            'advisorName' => $advisorDisplay ?: 'Asesor Agronómico',
            'asesorVentas' => $salesAdvisorDisplay,
            'AsesorVentasName' => $salesAdvisorDisplay,
            'asesorTecnico' => $advisorDisplay ?: 'Asesor Técnico',
            'AsesorTecnicoName' => $advisorDisplay ?: 'Asesor Técnico',
            'tecnicoCampo' => $advisorDisplay ?: 'Asesor Técnico',
            'SlpName' => $salesAdvisorDisplay,
            'advisorId' => $this->advisorId,
            'territorio' => $this->territory ?: 'N/D',
            'Territorio' => $this->territory ?: 'N/D',
            'zona' => $this->zone,
            'Zona' => $this->zone,

            // Cultivo
            'parcela' => $this->parcela,
            'Parcela' => $this->parcela,
            'superficie' => $superficie,
            'Hectareas' => $superficie,
            'cultivo' => $this->currentCropName ?: 'Cultivo',
            'CultivoActual' => $this->currentCropName ?: 'Cultivo',
            'fechaSiembra' => $this->estimatedSowingDate,
            'FechaSiembra' => $this->estimatedSowingDate,
            'semilla' => $this->seedVariety ?: 'N/A',
            'objetivoProduccion' => $this->targetYield ?? $this->currentCropTargetYield ?? 0,
            'coordenadas' => $coordStr,
            'Coordenadas' => $coordStr,
            'mapImage' => $this->mapImageBase64,
            'mapImageDataUrl' => $this->mapImageBase64,
            'Temporada' => $this->season,
            'TipoSuelo' => $this->soilType,

            // Etapas
            'etapas' => array_map(function ($stage) use ($superficie, $catalogMap) {
                $stageName = $stage['stageName'] ?? 'Etapa';
                $appMethod = $stage['applicationMethod'] ?? 'Tradicional';
                $appDate = $stage['applicationDate'] ?? null;
                $observations = $stage['observations'] ?? '';

                return [
                    'stageName' => $stageName,
                    'NombreEtapa' => $stageName,
                    'titulo' => $stageName,
                    'applicationMethod' => $appMethod,
                    'Metodo' => $appMethod,
                    'applicationDate' => $appDate,
                    'fechaEstimada' => $appDate,
                    'observations' => $observations,
                    'recomendaciones' => $observations,
                    'productos' => array_map(function ($prod) use ($superficie, $appMethod, $appDate, $observations, $catalogMap) {
                        $dose = (float) ($prod['dosePerHectare'] ?? 0);
                        $itemCode = (string) ($prod['itemCode'] ?? '');
                        $itemName = (string) ($prod['itemName'] ?? '');
                        $unit = (string) ($prod['unitOfMeasure'] ?? 'Kg/Ha');
                        $activeIng = ! empty($prod['activeIngredient']) ? $prod['activeIngredient'] : ($catalogMap[$itemCode] ?? 'No especificado');
                        $estNum = $superficie * $dose;
                        $estimatedPurchase = number_format($estNum, 2, '.', ',');

                        return [
                            'itemCode' => $itemCode,
                            'itemName' => $itemName,
                            'ItemName' => $itemName,
                            'producto' => $itemName ?: $itemCode,
                            'dosePerHectare' => $dose,
                            'dosis' => $dose,
                            'unitOfMeasure' => $unit,
                            'Unidad' => $unit,
                            'unitPrice' => (float) ($prod['unitPrice'] ?? 0),
                            'Precio' => (float) ($prod['unitPrice'] ?? 0),
                            'activeIngredient' => $activeIng,
                            'ingredienteActivo' => $activeIng,
                            'estimatedPurchase' => $estimatedPurchase,
                            'estimadoCompra' => $estimatedPurchase,
                            'metodo' => $prod['applicationMethod'] ?? $appMethod,
                            'fechaEstimada' => $appDate,
                            'recomendaciones' => $observations,
                        ];
                    }, $stage['products'] ?? []),
                ];
            }, $this->stages),
        ];
    }

    public function downloadProposalPdf(DomPdfService $pdfService): ?StreamedResponse
    {
        try {
            $planData = $this->getPlanDataForPdf();
            $pdfOutput = $pdfService->generateProposalPdf($planData);
            $versionNum = $this->isVersioning ? $this->newVersion : 1;
            $fileName = sprintf('PlanFertilizacion-%s-V%s.pdf', $this->savedPlanCode ?? 'PROPUESTA', $versionNum);

            return response()->streamDownload(
                fn () => print ($pdfOutput),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            Log::error('[PlanWizard] Error al descargar PDF', ['error' => $e->getMessage()]);
            $this->errorMessage = 'No se pudo generar el PDF para descarga: '.$e->getMessage();
            $this->showErrorModal = true;

            return null;
        }
    }

    public function getIsCustomerPhoneMissingProperty(): bool
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->customerPhone);

        return strlen($phone) < 10;
    }

    public function getSoilTypeNameProperty(): string
    {
        if (empty($this->soilType)) {
            return 'No especificado';
        }

        $matched = collect($this->soilTypes)->first(fn ($s) => (string) ($s['Code'] ?? $s['code'] ?? '') === (string) $this->soilType);

        return $matched['Name'] ?? $matched['name'] ?? 'Tipo '.$this->soilType;
    }

    public function shareViaWhatsApp(
        DomPdfService $pdfService,
        SendPlanWhatsAppUseCase $whatsAppUseCase,
        FertilizationPlanRepositoryInterface $planRepository
    ): void {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->customerPhone);
        if (strlen($phone) < 10) {
            $this->whatsAppError = 'No se puede compartir: El cliente no cuenta con número celular registrado en SAP.';

            return;
        }

        $this->isSharingWhatsApp = true;
        $this->whatsAppSuccess = null;
        $this->whatsAppError = null;

        try {
            $planData = $this->getPlanDataForPdf();
            $pdfOutput = $pdfService->generateProposalPdf($planData);
            $base64 = base64_encode($pdfOutput);

            $fileName = sprintf('Plan_Fertilizacion_%s.pdf', $this->customerName ?: 'Cliente');
            $customMessage = sprintf(
                "🌱 *¡Tu Plan de Fertilización está listo!* 🌱\n\nHola 👋\nTe compartimos tu Plan de Fertilización para *%s* (Folio: *%s*).\n\n📄 Adjunto encontrarás el documento con las recomendaciones agronómicas.\n\n¡Estamos a tus órdenes! 🌾",
                $this->customerName ?: 'Cliente',
                $this->savedPlanCode ?? 'N/A'
            );

            $dto = new WhatsAppMessageDTO(
                recipientPhone: $phone,
                customerName: $this->customerName ?: 'Cliente',
                pdfBase64: $base64,
                fileName: $fileName,
                customMessage: $customMessage
            );

            $result = $whatsAppUseCase->execute($dto);

            if (! empty($result['success'])) {
                $this->whatsAppSuccess = $result['message'] ?? 'Plan enviado al cliente exitosamente.';

                if ($this->savedPlanCode) {
                    $planRepository->savePlanPdf(
                        planCode: (string) $this->savedPlanCode,
                        version: $this->isVersioning ? $this->newVersion : 1,
                        status: 'Activo',
                        base64File: $base64
                    );
                }
            } else {
                $this->whatsAppError = $result['message'] ?? 'No se pudo enviar el documento por WhatsApp.';
            }
        } catch (\Throwable $e) {
            Log::error('[PlanWizard] Error al compartir WhatsApp', ['error' => $e->getMessage()]);
            $this->whatsAppError = 'Error al enviar por WhatsApp: '.$e->getMessage();
        } finally {
            $this->isSharingWhatsApp = false;
        }
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->redirect($this->isVersioning ? route('seguimiento.index') : route('dashboard'));
    }

    public function closeErrorModal(): void
    {
        $this->showErrorModal = false;
    }

    public function render()
    {
        return view('livewire.wizard.plan-wizard')->layout('layouts.app');
    }
}

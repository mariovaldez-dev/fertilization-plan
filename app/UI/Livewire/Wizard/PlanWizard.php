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

    public function setMapImage(string $base64): void
    {
        $this->mapImageBase64 = $base64;
    }

    public function mount(): void
    {
        $this->estimatedSowingDate = null;
    }

    public function loadInitialData(CatalogsRepositoryInterface $catalogs): void
    {
        $this->zones = $catalogs->getZones();
        $this->soilTypes = $catalogs->getSoilTypes();
        $this->cropTypes = $catalogs->getCropTypes();
        $this->availableProducts = $catalogs->getProducts();
        $this->applicationMethods = $catalogs->getApplicationMethods();
        $this->isLoaded = true;
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
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            Log::warning('[PlanWizard] Validación fallida al guardar plan', [
                'errors' => $errors->toArray(),
            ]);

            if ($errors->hasAny(['customerCode', 'advisorId', 'parcela', 'hectares', 'soilType', 'analisisSuelo', 'latitude', 'longitude'])) {
                $this->currentStep = 1;
            } elseif ($errors->hasAny(['previousCrop', 'previousCropYield', 'previousCropMaxYield', 'currentCropCode', 'seedVariety', 'estimatedSowingDate', 'targetYield'])) {
                $this->currentStep = 2;
            }

            throw $e;
        }

        try {
            $targetYieldVal = $this->targetYield ?? $this->currentCropTargetYield;
            $hasSoilAnalysis = strtoupper((string) $this->analisisSuelo) === 'SI';

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

        return [
            // General
            'planCode' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'PlanCode' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'id' => (string) ($this->savedPlanCode ?? 'PF-NUEVO'),
            'mensaje' => $planMessage,
            'version' => 1,
            'Version' => 1,
            'customerName' => $this->customerName,
            'productor' => $this->customerName,
            'CardName' => $this->customerName,
            'CardCode' => $this->customerCode,
            'advisorName' => $this->advisorName,
            'asesorVentas' => $this->advisorName,
            'asesorTecnico' => $this->advisorName,
            'tecnicoCampo' => $this->advisorName,
            'SlpName' => $this->advisorName,
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
            $fileName = sprintf('PlanFertilizacion-%s-V1.pdf', $this->savedPlanCode ?? 'PROPUESTA');

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

    public function shareViaWhatsApp(
        DomPdfService $pdfService,
        SendPlanWhatsAppUseCase $whatsAppUseCase,
        FertilizationPlanRepositoryInterface $planRepository
    ): void {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->advisorPhone);
        if (strlen($phone) < 10) {
            $this->whatsAppError = 'Ingresa un número telefónico válido (mínimo 10 dígitos) para enviar por WhatsApp.';

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
                "🌱 *Plan de Fertilización disponible* 🌱\nHola %s 👋\n\nTe compartimos tu Plan de Fertilización (Folio: *%s*) para el predio *%s* con las recomendaciones agronómicas calculadas.\n\n📄 Adjunto encontrarás el documento en formato PDF.\n\n¡Gracias por tu confianza! 🌾",
                $this->customerName ?: 'Productor',
                $this->savedPlanCode ?? 'N/A',
                $this->parcela ?: 'Lote'
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
                $this->whatsAppSuccess = $result['message'] ?? '¡Plan enviado por WhatsApp exitosamente!';

                if ($this->savedPlanCode) {
                    $planRepository->savePlanPdf(
                        planCode: (string) $this->savedPlanCode,
                        version: 1,
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
        if (! empty($this->customerCode)) {
            $this->redirect(route('seguimiento.planes', ['cardCode' => $this->customerCode]));
        } else {
            $this->redirect(route('seguimiento.index'));
        }
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

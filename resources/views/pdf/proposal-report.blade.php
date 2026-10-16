<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan de Fertilización</title>
    <style>
        @page {
            margin: 15mm 15mm 18mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        .w-full {
            width: 100%;
        }
        .header-table {
            width: 100%;
            margin-bottom: 6px;
        }
        .header-title {
            font-size: 18pt;
            font-weight: bold;
            color: #006400;
            letter-spacing: -0.5px;
        }
        .header-line {
            border-bottom: 1.5px solid #006400;
            margin-top: 6px;
            margin-bottom: 12px;
            width: 100%;
        }
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #505050;
            border-bottom: 0.8px solid #c8c8c8;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .info-table {
            width: 100%;
            font-size: 8.2pt;
        }
        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .info-label {
            width: 95px;
            font-weight: bold;
            color: #000000;
        }
        .info-value {
            color: #505050;
        }
        .stages-banner {
            background-color: #f5f5f5;
            border-radius: 4px;
            padding: 5px 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #505050;
        }
        .stage-header-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .stage-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #006400;
        }
        .stage-date {
            font-size: 8pt;
            font-style: italic;
            color: #505050;
            text-align: right;
        }
        .product-card {
            border: 0.8px solid #c8c8c8;
            border-radius: 5px;
            padding: 6px 8px;
            margin-bottom: 6px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }
        .prod-grid-table {
            width: 100%;
            font-size: 7.8pt;
        }
        .prod-grid-table td {
            vertical-align: top;
        }
        .prod-info-table {
            width: 100%;
        }
        .prod-info-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .prod-label {
            width: 75px;
            font-weight: bold;
            color: #000000;
        }
        .prod-value {
            color: #505050;
        }
        .card-header-label {
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
            font-size: 8pt;
        }
        .active-ingredient {
            color: #006400;
            font-size: 8pt;
            line-height: 1.25;
        }
        .recommendations {
            color: #505050;
            font-size: 7.8pt;
            line-height: 1.25;
        }
        .map-box {
            width: 100%;
            height: 90px;
            background-color: #f5f5f5;
            border: 0.8px solid #c8c8c8;
            border-radius: 4px;
            text-align: center;
            line-height: 90px;
            font-size: 8.5pt;
            color: #969696;
            margin-top: 8px;
        }
        .map-img {
            width: 100%;
            height: 90px;
            border-radius: 4px;
            border: 0.8px solid #c8c8c8;
            margin-top: 8px;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">Plan de Fertilización</div>
            </td>
            <td style="vertical-align: middle; text-align: right; width: 150px;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="height: 32px; width: auto;" alt="Logo Grupo Impulsora">
                @else
                    <span style="font-size: 9pt; color: #969696;">[Logo]</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="header-line"></div>

    {{-- SECCIÓN 1: DATOS GENERALES Y CULTIVO --}}
    <table class="w-full" style="table-layout: fixed;">
        <tr>
            {{-- Columna 1: Datos Generales --}}
            <td style="width: 48%; vertical-align: top;">
                <div class="section-title">Datos Generales</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Productor:</td>
                        <td class="info-value">{{ $plan['productor'] ?? $plan['customerName'] ?? $plan['CardName'] ?? 'N/A' }}</td>
                    </tr>
                    @php
                        $asesorTecnicoDisplay = (string) ($plan['tecnicoCampo'] ?? $plan['asesorTecnico'] ?? $plan['AsesorTecnicoName'] ?? $plan['advisorName'] ?? 'N/A');
                        if (str_contains($asesorTecnicoDisplay, '@')) {
                            $prefix = explode('@', $asesorTecnicoDisplay)[0];
                            $words = preg_split('/[._-]/', $prefix);
                            $asesorTecnicoDisplay = ucwords(implode(' ', array_filter($words ?: [$prefix])));
                        }

                        $sessionUserName = (string) (session('authenticated_user')?->name ?? '');
                        if (str_contains($sessionUserName, '@')) {
                            $prefix = explode('@', $sessionUserName)[0];
                            $words = preg_split('/[._-]/', $prefix);
                            $sessionUserName = ucwords(implode(' ', array_filter($words ?: [$prefix])));
                        }

                        $asesorVentasDisplay = (string) ($plan['asesorVentas'] ?? $plan['AsesorVentasName'] ?? '');
                        if (str_contains($asesorVentasDisplay, '@')) {
                            $prefix = explode('@', $asesorVentasDisplay)[0];
                            $words = preg_split('/[._-]/', $prefix);
                            $asesorVentasDisplay = ucwords(implode(' ', array_filter($words ?: [$prefix])));
                        }

                        if (empty($asesorVentasDisplay) || $asesorVentasDisplay === $asesorTecnicoDisplay) {
                            $asesorVentasDisplay = !empty($sessionUserName) ? $sessionUserName : ($plan['SlpName'] ?? 'Asesor Comercial');
                        }
                    @endphp
                    <tr>
                        <td class="info-label">Asesor Ventas:</td>
                        <td class="info-value">{{ $asesorVentasDisplay }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Asesor Técnico:</td>
                        <td class="info-value">{{ $asesorTecnicoDisplay }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Territorio:</td>
                        <td class="info-value">{{ $plan['territorio'] ?? $plan['Territorio'] ?? 'No disponible' }}</td>
                    </tr>
                </table>

                {{-- QR Code e Identificador --}}
                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="width: 100px; vertical-align: middle;">
                            @if(!empty($qrCode))
                                <img src="{{ $qrCode }}" style="width: 95px; height: 95px; display: block;" alt="QR Code">
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding-left: 8px;">
                            <div style="font-family: 'Courier New', Courier, monospace; font-size: 9pt; font-weight: bold; color: #505050; word-break: break-all; line-height: 1.35;">
                                {{ $plan['mensaje'] ?? $plan['PlanCode'] ?? $plan['planCode'] ?? $plan['id'] ?? 'PF-NUEVO' }}
                            </div>
                            <div style="font-size: 7.2pt; color: #969696; margin-top: 3px;">
                                Identificador del Plan
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Separador --}}
            <td style="width: 4%;"></td>

            {{-- Columna 2: Datos de Cultivo --}}
            <td style="width: 48%; vertical-align: top;">
                <div class="section-title">Datos de Cultivo</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Parcela:</td>
                        <td class="info-value">{{ $plan['parcela'] ?? $plan['Parcela'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Hectáreas:</td>
                        <td class="info-value">
                            @php
                                $sup = $plan['superficie'] ?? $plan['Hectareas'] ?? null;
                            @endphp
                            {{ !empty($sup) ? $sup . ' ha' : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Cultivo:</td>
                        <td class="info-value">{{ $plan['cultivo'] ?? $plan['CultivoActual'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Fecha Siembra:</td>
                        <td class="info-value">
                            @php
                                $fs = $plan['fechaSiembra'] ?? $plan['FechaSiembra'] ?? null;
                            @endphp
                            {{ !empty($fs) ? \Carbon\Carbon::parse($fs)->format('d/m/Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Semilla/Var.:</td>
                        <td class="info-value">{{ $plan['semilla'] ?? $plan['seedVariety'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Objetivo Prod.:</td>
                        <td class="info-value">
                            @php
                                $obj = $plan['objetivoProduccion'] ?? null;
                            @endphp
                            {{ !empty($obj) ? $obj . ' Ton/ha' : 'N/A' }}
                        </td>
                    </tr>
                </table>

                {{-- Sección Mapa --}}
                @if(!empty($mapBase64))
                    <img src="{{ $mapBase64 }}" class="map-img" alt="Mapa">
                @else
                    <div class="map-box">Mapa no disponible</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- TÍTULO SECCIÓN DESGLOSE --}}
    <div class="stages-banner">
        Desglose del Plan por Etapas
    </div>

    {{-- BUCLE DE ETAPAS --}}
    @if(!empty($plan['etapas']))
        @foreach($plan['etapas'] as $etapa)
            @php
                $prods = $etapa['productos'] ?? $etapa['products'] ?? [];
                if (empty($prods)) continue;
                $primerProd = reset($prods);
                $fechaEtapa = $etapa['fechaEstimada'] ?? $etapa['applicationDate'] ?? ($primerProd['fechaEstimada'] ?? null);
            @endphp

            <table class="stage-header-table">
                <tr>
                    <td class="stage-title">
                        {{ $etapa['titulo'] ?? $etapa['stageName'] ?? $etapa['NombreEtapa'] ?? 'Etapa' }}
                    </td>
                    <td class="stage-date">
                        Fecha aplic.: {{ !empty($fechaEtapa) ? \Carbon\Carbon::parse($fechaEtapa)->format('d/m/Y') : 'N/D' }}
                    </td>
                </tr>
            </table>

            {{-- DIBUJA CADA PRODUCTO COMO UNA TARJETA --}}
            @foreach($prods as $prod)
                @php
                    $dosisNum = (float)($prod['dosis'] ?? $prod['dosePerHectare'] ?? 0);
                    $dosisStr = number_format($dosisNum, 2);
                    $unidadProd = $prod['unitOfMeasure'] ?? $prod['Unidad'] ?? 'Ton/Ha';
                    $unidadEst = str_contains($unidadProd, 'Kg') ? 'Kg' : (str_contains($unidadProd, 'L') ? 'L' : 'Ton');
                    
                    $estStr = $prod['estimadoCompra'] ?? $prod['estimatedPurchase'] ?? null;
                    if (empty($estStr)) {
                        $supNum = (float)($plan['superficie'] ?? $plan['Hectareas'] ?? 1);
                        $estStr = number_format($supNum * $dosisNum, 2);
                    }
                    
                    $fechaProd = $prod['fechaEstimada'] ?? $fechaEtapa;
                @endphp

                <div class="product-card">
                    <table class="prod-grid-table">
                        <tr>
                            {{-- Columna 1: Info Producto --}}
                            <td style="width: 44%;">
                                <table class="prod-info-table">
                                    <tr>
                                        <td class="prod-label">Producto:</td>
                                        <td class="prod-value" style="font-weight: bold; color: #000000;">
                                            {{ $prod['producto'] ?? $prod['itemName'] ?? $prod['ItemName'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="prod-label">Fecha Aplic.:</td>
                                        <td class="prod-value">
                                            {{ !empty($fechaProd) ? \Carbon\Carbon::parse($fechaProd)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="prod-label">Método:</td>
                                        <td class="prod-value">
                                            {{ $prod['metodo'] ?? $etapa['Metodo'] ?? $etapa['applicationMethod'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="prod-label">Dosis:</td>
                                        <td class="prod-value">
                                            {{ $dosisStr }} {{ $unidadProd }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="prod-label">Est. Compra:</td>
                                        <td class="prod-value">
                                            {{ $estStr }} {{ $unidadEst }}
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            {{-- Columna 2: Ingrediente Activo --}}
                            <td style="width: 26%; padding-left: 8px;">
                                <div class="card-header-label">Ing. Activo:</div>
                                <div class="active-ingredient">
                                    {{ $prod['ingredienteActivo'] ?? $prod['activeIngredient'] ?? 'No especificado' }}
                                </div>
                            </td>

                            {{-- Columna 3: Recomendaciones --}}
                            <td style="width: 30%; padding-left: 8px;">
                                <div class="card-header-label">Recomendaciones:</div>
                                <div class="recommendations">
                                    {{ $prod['recomendaciones'] ?? $etapa['observations'] ?? $etapa['recomendaciones'] ?? 'N/A' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        @endforeach
    @endif

    {{-- PIE DE PÁGINA (SCRIPT PHP DOMPDF) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 8;
            $color = [150 / 255, 150 / 255, 150 / 255];
            $border = [200 / 255, 200 / 255, 200 / 255];
            
            $pageWidth = $pdf->get_width();
            $pageHeight = $pdf->get_height();
            $margin = 42.5; // ~15mm in points
            
            // Draw line on every page
            $lineY = $pageHeight - 28;
            $pdf->line($margin, $lineY, $pageWidth - $margin, $lineY, $border, 0.5);
            
            // Text generated date
            $dateText = "Generado: " . date("d/m/Y");
            $pdf->page_text($margin, $pageHeight - 20, $dateText, $font, $size, $color);
            
            // Page X of Y
            $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
            $textWidth = $fontMetrics->get_text_width($pageText, $font, $size);
            $pdf->page_text($pageWidth - $margin - $textWidth, $pageHeight - 20, $pageText, $font, $size, $color);
        }
    </script>
</body>
</html>

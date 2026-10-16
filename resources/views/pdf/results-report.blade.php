<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dictamen de Resultados Agronómicos — Grupo Impulsora</title>
    <style>
        @page {
            margin: 12mm 14mm 15mm 14mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1a1a1a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table {
            margin-bottom: 4px;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            color: #056a39;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            font-size: 9pt;
            color: #4b5563;
            font-weight: normal;
        }
        .header-line {
            border-bottom: 2px solid #056a39;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #056a39;
            border-bottom: 1px solid #d9e8dd;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
            font-size: 8pt;
        }
        .info-label {
            font-weight: bold;
            color: #056a39;
            width: 32%;
        }
        .info-value {
            color: #111827;
        }
        .map-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-align: center;
            overflow: hidden;
            background-color: #f8fafc;
        }
        .qr-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px;
            background-color: #ffffff;
            display: inline-block;
        }
        .kpi-grid-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .kpi-card-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 2px;
        }
        .kpi-card-val {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-card-val.highlight {
            color: #056a39;
        }
        .kpi-card-val.trad {
            color: #b45309;
        }
        .stages-table {
            margin-top: 6px;
            border: 1px solid #e2e8f0;
        }
        .stages-table th {
            background-color: #f0fdf4;
            color: #056a39;
            font-size: 8pt;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .stages-table td {
            font-size: 7.8pt;
            padding: 4.5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .comment-box {
            border: 1px solid #d9e8dd;
            border-radius: 4px;
            padding: 6px 8px;
            background-color: #fcfdfc;
            height: 60px;
        }
        .comment-box-title {
            font-size: 8pt;
            font-weight: bold;
            color: #056a39;
            margin-bottom: 3px;
        }
        .comment-box-text {
            font-size: 7.5pt;
            color: #374151;
            line-height: 1.25;
        }
        .footer {
            margin-top: 14px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $plan = $data['planInfo'] ?? [];
        $planCode = $plan['PlanCode'] ?? 'PF-000';
        $version = $plan['U_Version'] ?? $plan['Version'] ?? 1;
        $parcela = $plan['U_Parcela'] ?? $plan['Parcela'] ?? 'General';
        $cultivo = $plan['CultivoName'] ?? $plan['CultivoActual'] ?? 'Cultivo';
        $superficie = $plan['U_Superficie'] ?? $plan['Superficie'] ?? $plan['Hectareas'] ?? 0;
        $costoReal = (float)($plan['U_CostoTotal'] ?? $plan['CostoPorHa'] ?? 0);
        $costoTrad = (float)($plan['U_Costo_Trad'] ?? $plan['CostoTradicional'] ?? 0);
        $rendimiento = (float)($plan['RendimientoObtenido'] ?? $plan['U_Rendimiento'] ?? $plan['RendimientoFinal'] ?? 0);
        $rendimientoTrad = (float)($plan['U_Rendimiento_Trad'] ?? $plan['RendimientoTestigo'] ?? 0);
        $dosis = (float)($plan['U_Dosis'] ?? 1);
        $precioCosecha = (float)($plan['U_Precio_Cosecha'] ?? 0);
        $utilidadExtra = (float)($plan['U_Utilidad_Ext'] ?? (($rendimiento - $rendimientoTrad) * $precioCosecha));
        $inversionExtra = (float)($plan['U_Inversion_Ext'] ?? ($costoReal - $costoTrad));
        $roi = (float)($plan['U_Retorno_Inv'] ?? ($inversionExtra != 0 ? $utilidadExtra / $inversionExtra : 0));
        $seguimiento = $data['seguimiento'] ?? [];
        $nutricional = $data['nutricional'] ?? [];
    @endphp

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 65%; vertical-align: middle;">
                <div class="header-title">Plan de Fertilización</div>
                <div class="header-subtitle">Dictamen Final de Cosecha & Análisis de Rentabilidad Agronómica</div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="height: 32px; width: auto;" alt="Grupo Impulsora">
                @else
                    <span style="font-size: 13pt; font-weight: bold; color: #056a39;">GRUPO IMPULSORA</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="header-line"></div>

    <!-- General Data and Crop Information (2 Columns) -->
    <table>
        <tr>
            <!-- Col 1: Datos Generales -->
            <td style="width: 49%; vertical-align: top; padding-right: 8px;">
                <div class="section-title">Datos Generales</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Productor:</td>
                        <td class="info-value"><strong>{{ $plan['CardName'] ?? 'Productor' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Zona:</td>
                        <td class="info-value">{{ $plan['Zona'] ?? 'N/D' }}</td>
                    </tr>
                    @php
                        $resAsesorTecnico = (string) ($plan['AsesorTecnicoName'] ?? $plan['Asesor'] ?? $plan['AdvisorName'] ?? 'N/D');
                        $resAsesorVentas = (string) ($plan['AsesorVentasName'] ?? $plan['AsesorVentas'] ?? '');
                        if (empty($resAsesorVentas) || $resAsesorVentas === 'N/D' || $resAsesorVentas === $resAsesorTecnico) {
                            $resAsesorVentas = (string) (session('authenticated_user')?->name ?? 'Asesor Comercial');
                        }
                    @endphp
                    <tr>
                        <td class="info-label">Asesor ventas:</td>
                        <td class="info-value">{{ $resAsesorVentas }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Municipio:</td>
                        <td class="info-value">{{ $plan['Municipio'] ?? 'N/D' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Asesor Técnico:</td>
                        <td class="info-value">{{ $resAsesorTecnico }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Territorio:</td>
                        <td class="info-value">{{ $plan['Territorio'] ?? 'N/D' }}</td>
                    </tr>
                </table>

                <!-- QR & Folio Identificador -->
                <div style="margin-top: 8px;">
                    <table>
                        <tr>
                            <td style="width: 65px; vertical-align: middle;">
                                @if(!empty($qrCode))
                                    <div class="qr-box">
                                        <img src="{{ $qrCode }}" style="width: 58px; height: 58px;" alt="QR Code">
                                    </div>
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding-left: 8px;">
                                <div style="font-size: 10.5pt; font-weight: bold; color: #056a39;">
                                    Folio #{{ $planCode }}
                                </div>
                                <div style="font-size: 8pt; color: #4b5563;">
                                    Versión V{{ $version }} — {{ $parcela }}
                                </div>
                                <div style="font-size: 7.5pt; color: #6b7280; margin-top: 2px;">
                                    Verificación Digital Grupo Impulsora
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <!-- Col 2: Datos de Cultivo & Ubicación -->
            <td style="width: 51%; vertical-align: top; padding-left: 8px;">
                <div class="section-title">Datos del Cultivo & Ubicación</div>
                <table>
                    <tr>
                        <td style="width: 55%; vertical-align: top;">
                            <table class="info-table">
                                <tr>
                                    <td class="info-label">Parcela:</td>
                                    <td class="info-value">{{ $parcela }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Superficie:</td>
                                    <td class="info-value">{{ $superficie }} Ha</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Cultivo:</td>
                                    <td class="info-value"><strong>{{ $cultivo }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Fecha siembra:</td>
                                    <td class="info-value">{{ !empty($plan['U_Fecha_Siembra']) ? substr((string)$plan['U_Fecha_Siembra'], 0, 10) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Variedad:</td>
                                    <td class="info-value">{{ $plan['U_Variedad_Semilla'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Meta Prod:</td>
                                    <td class="info-value">{{ $plan['U_Objetivo_Prod'] ?? 'N/A' }} Ton/Ha</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 45%; vertical-align: top; text-align: right;">
                            <div class="map-box" style="width: 145px; height: 95px; margin-left: auto;">
                                @if(!empty($mapBase64))
                                    <img src="{{ $mapBase64 }}" style="width: 145px; height: 95px; object-fit: cover;" alt="Mapa Parcela">
                                @else
                                    <div style="font-size: 7pt; color: #94a3b8; padding-top: 35px;">Mapa Satelital No Disponible</div>
                                @endif
                            </div>
                            <div style="font-size: 6.8pt; color: #64748b; text-align: right; margin-top: 3px;">
                                Coordenadas: {{ substr($plan['U_Coordenadas'] ?? $plan['Coordenadas'] ?? 'N/D', 0, 24) }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Resultado de la Cosecha & Rentabilidad (Tarjetas Bento) -->
    <div class="section-title">Resultados de Cosecha & Comparativa de Rentabilidad</div>
    <table class="kpi-grid-table">
        <tr style="background-color: #f8fafc;">
            <td style="width: 20%;">
                <span class="kpi-card-title">Fecha Cosecha</span>
                <span class="kpi-card-val" style="font-size: 9.5pt;">{{ !empty($plan['U_FechaCaptura']) ? substr((string)$plan['U_FechaCaptura'], 0, 10) : date('Y-m-d') }}</span>
            </td>
            <td style="width: 20%; background-color: #f0fdf4;">
                <span class="kpi-card-title">Rendimiento Impulsora</span>
                <span class="kpi-card-val highlight">{{ number_format($rendimiento, 1) }} <small style="font-size: 7pt;">Ton/Ha</small></span>
            </td>
            <td style="width: 20%; background-color: #fffbeb;">
                <span class="kpi-card-title">Rend. Tradicional</span>
                <span class="kpi-card-val trad">{{ number_format($rendimientoTrad, 1) }} <small style="font-size: 7pt;">Ton/Ha</small></span>
            </td>
            <td style="width: 20%; background-color: #f0fdf4;">
                <span class="kpi-card-title">Costo Impulsora</span>
                <span class="kpi-card-val highlight">${{ number_format($costoReal, 2) }}</span>
            </td>
            <td style="width: 20%; background-color: #fffbeb;">
                <span class="kpi-card-title">Costo Tradicional</span>
                <span class="kpi-card-val trad">${{ number_format($costoTrad, 2) }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="kpi-card-title">Dosis Aplicada</span>
                <span class="kpi-card-val" style="font-size: 9.5pt;">{{ number_format($dosis, 2) }} <small style="font-size: 7pt;">Ton/Ha</small></span>
            </td>
            <td>
                <span class="kpi-card-title">Precio de Cosecha</span>
                <span class="kpi-card-val" style="font-size: 9.5pt;">${{ number_format($precioCosecha, 2) }} <small style="font-size: 7pt;">/Ton</small></span>
            </td>
            <td style="background-color: #ecfdf5;">
                <span class="kpi-card-title" style="color: #056a39;">Utilidad Extra</span>
                <span class="kpi-card-val" style="color: #056a39;">+${{ number_format($utilidadExtra, 2) }}</span>
            </td>
            <td style="background-color: #eff6ff;">
                <span class="kpi-card-title" style="color: #1d4ed8;">Inversión Extra</span>
                <span class="kpi-card-val" style="color: #1d4ed8;">${{ number_format($inversionExtra, 2) }}</span>
            </td>
            <td style="background-color: #f5f3ff;">
                <span class="kpi-card-title" style="color: #6d28d9;">Retorno ROI</span>
                <span class="kpi-card-val" style="color: #6d28d9;">{{ number_format($roi, 2) }}x</span>
            </td>
        </tr>
    </table>

    <!-- Historial de Aplicaciones (Seguimiento) -->
    <div class="section-title">Historial de Aplicaciones Realizadas</div>
    @if(!empty($seguimiento) && is_array($seguimiento))
        <table class="stages-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Etapa Fenológica</th>
                    <th style="width: 15%;">Fecha Estimada</th>
                    <th style="width: 20%;">Método de Aplicación</th>
                    <th style="width: 40%;">Productos Aplicados (Dosis & Precio)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seguimiento as $etapa)
                    @php
                        $etapaNombre = $etapa['U_Name_Etapa'] ?? $etapa['Etapa'] ?? 'Etapa';
                        $fechaApl = !empty($etapa['U_Fecha_Aplicacion']) ? substr((string)$etapa['U_Fecha_Aplicacion'], 0, 10) : 'N/A';
                        $metodo = $etapa['MetodoAplicacion'] ?? $etapa['U_ID_Aplicacion'] ?? 'Foliar / Fertirriego';
                        $productosRaw = $etapa['ProductosDesc'] ?? [];
                        if (is_string($productosRaw)) {
                            $decoded = json_decode($productosRaw, true);
                            $productos = is_array($decoded) ? $decoded : [];
                        } else {
                            $productos = is_array($productosRaw) ? $productosRaw : [];
                        }
                    @endphp
                    <tr>
                        <td><strong>{{ $etapaNombre }}</strong></td>
                        <td>{{ $fechaApl }}</td>
                        <td>{{ $metodo }}</td>
                        <td>
                            @if(!empty($productos))
                                @foreach($productos as $p)
                                    <div>
                                        • <strong>{{ $p['id'] ?? $p['ItemCode'] ?? 'Producto' }}</strong>
                                        @if(isset($p['dosis'])) — Dosis: {{ $p['dosis'] }} @endif
                                        @if(isset($p['Precio'])) — ${{ number_format((float)$p['Precio'], 2) }} @endif
                                        @if(!empty($p['Proveedor'])) <span style="color: #64748b;">({{ $p['Proveedor'] }})</span> @endif
                                    </div>
                                @endforeach
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Sin productos detallados</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="font-size: 8pt; color: #64748b; font-style: italic; padding: 6px 0;">
            No se registraron etapas adicionales en el historial de seguimiento.
        </div>
    @endif

    <!-- Aporte Nutricional Si Existe -->
    @if(!empty($nutricional) && is_array($nutricional))
        <div class="section-title">Aporte Nutricional / Ingredientes Activos</div>
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; font-size: 7.8pt; color: #334155;">
            @php
                $nutrientes = array_filter(array_map(function($n) {
                    return $n['Nutriente'] ?? $n['U_Det_IngActivo'] ?? null;
                }, $nutricional));
            @endphp
            <strong>Elementos Aportados:</strong> {{ !empty($nutrientes) ? implode('  •  ', array_unique($nutrientes)) : 'Nutrientes balanceados según etapa fenológica.' }}
        </div>
    @endif

    <!-- Comentarios y Recomendaciones Técnicas (3 Columnas) -->
    <div class="section-title">Dictamen Técnico & Recomendaciones Agronómicas</div>
    <table>
        <tr>
            <td style="width: 33.3%; padding-right: 4px; vertical-align: top;">
                <div class="comment-box">
                    <div class="comment-box-title">Recomendaciones Técnicas</div>
                    <div class="comment-box-text">
                        {{ $plan['U_Recomendaciones'] ?? 'Mantener monitoreo de humedad y balance nutricional para consolidar la calidad del suelo.' }}
                    </div>
                </div>
            </td>
            <td style="width: 33.3%; padding: 0 4px; vertical-align: top;">
                <div class="comment-box">
                    <div class="comment-box-title">Propuesta Siguiente Ciclo</div>
                    <div class="comment-box-text">
                        {{ $plan['U_Propuesta'] ?? 'Implementar programa de nutrición integral Grupo Impulsora con fertilización foliar y bioestimulantes.' }}
                    </div>
                </div>
            </td>
            <td style="width: 33.3%; padding-left: 4px; vertical-align: top;">
                <div class="comment-box">
                    <div class="comment-box-title">Buenas Prácticas Aplicadas</div>
                    <div class="comment-box-text">
                        {{ $plan['U_BuenasPracticas'] ?? 'Calibración oportuna de boquillas, manejo eficiente del fertirriego y monitoreo de fenología.' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dictamen Técnico Emitido por el Departamento Agronómico de Grupo Impulsora • Generado el {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>

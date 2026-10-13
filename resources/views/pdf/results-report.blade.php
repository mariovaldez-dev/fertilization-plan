<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dictamen de Resultados Agronómicos</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1a202c;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #4a5568;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .comparison-table th {
            background-color: #f1f5f9;
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
        }
        .comparison-table td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-size: 10px;
        }
        .highlight-impulsora {
            background-color: #f0fdf4;
            font-weight: bold;
            color: #166534;
        }
        .highlight-testigo {
            background-color: #fffbeb;
            font-weight: bold;
            color: #92400e;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 6px;
            border-left: 3px solid #2563eb;
            padding-left: 6px;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="title">Grupo Impulsora — Dictamen de Resultados</div>
                <div class="subtitle">Comparativa Agronómica: Manejo Impulsora vs. Lote Testigo (IMP-07)</div>
            </td>
            <td style="text-align: right;">
                @if(!empty($qrCode))
                    <img src="{{ $qrCode }}" width="50" height="50" alt="QR Code">
                @endif
            </td>
        </tr>
    </table>

    <div class="info-card">
        <table width="100%">
            <tr>
                <td><strong>Cliente:</strong> {{ $data['planInfo']['CardName'] ?? 'Productor' }}</td>
                <td><strong>Folio Plan:</strong> {{ $data['planInfo']['PlanCode'] ?? 'PF-000' }}</td>
                <td><strong>Cultivo:</strong> {{ $data['planInfo']['CultivoActual'] ?? '' }}</td>
                <td><strong>Superficie:</strong> {{ $data['planInfo']['Hectareas'] ?? 1 }} Ha</td>
            </tr>
        </table>
    </div>

    <!-- Comparative KPI Table -->
    <div class="section-title">Resumen Comparativo de Eficiencia Agronómica</div>
    <table class="comparison-table">
        <thead>
            <tr>
                <th>Métrica Agronómica</th>
                <th style="color: #166534;">Manejo Grupo Impulsora</th>
                <th style="color: #92400e;">Lote Testigo (Tradicional)</th>
                <th>Diferencial / Beneficio</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Rendimiento Obtenido</strong></td>
                <td class="highlight-impulsora">{{ number_format((float)($data['planInfo']['RendimientoFinal'] ?? 0), 2) }} Ton/Ha</td>
                <td class="highlight-testigo">{{ number_format((float)($data['planInfo']['RendimientoTestigo'] ?? 0), 2) }} Ton/Ha</td>
                <td style="font-weight: bold; color: #16a34a;">+{{ number_format((float)($data['planInfo']['RendimientoFinal'] ?? 0) - (float)($data['planInfo']['RendimientoTestigo'] ?? 0), 2) }} Ton/Ha</td>
            </tr>
            <tr>
                <td><strong>Costo por Hectárea ($/Ha)</strong></td>
                <td class="highlight-impulsora">${{ number_format((float)($data['planInfo']['CostoPorHa'] ?? 0), 2) }}</td>
                <td class="highlight-testigo">${{ number_format((float)($data['planInfo']['CostoTradicional'] ?? 0), 2) }}</td>
                <td style="font-weight: bold; color: #2563eb;">Ahorro: ${{ number_format((float)($data['planInfo']['CostoTradicional'] ?? 0) - (float)($data['planInfo']['CostoPorHa'] ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dictamen emitido por el Departamento Técnico de Grupo Impulsora.
    </div>
</body>
</html>

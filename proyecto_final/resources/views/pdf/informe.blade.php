<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $informe->nombre ?? 'Informe de Pasantía' }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-bottom: 2px solid #010C67;
            padding-bottom: 10px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #010C67;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 12px;
            color: #555;
            margin-top: 3px;
        }
        .meta-section {
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
            padding: 3px 0;
            font-size: 10px;
        }
        .meta-val {
            color: #0f172a;
            width: 75%;
            padding: 3px 0;
            font-size: 10px;
        }
        .intro-text {
            font-size: 11px;
            color: #334155;
            margin-bottom: 20px;
            text-align: justify;
        }
        .bitacora-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .bitacora-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            font-size: 10px;
            color: #1e293b;
        }
        .bitacora-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .text-nowrap {
            white-space: nowrap;
        }
        .evidencias-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .evidencias-title {
            font-size: 12px;
            font-weight: bold;
            color: #010C67;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            @if(!empty($logoBase64))
                <td style="width: 75px; vertical-align: middle;">
                    <img src="{{ $logoBase64 }}" style="width: 65px; height: auto;" />
                </td>
            @endif
            <td style="vertical-align: middle; padding-left: 10px;">
                <div class="header-title">UNIVERSIDAD GERARDO BARRIOS</div>
                <div class="header-subtitle">Ingeniería en Sistemas y Redes Informáticas</div>
            </td>
        </tr>
    </table>

    <div class="meta-section">
        <table class="meta-grid">
            <tr>
                <td class="meta-label">Estudiante:</td>
                <td class="meta-val">{{ $pasante->usuario->nombres ?? '' }} {{ $pasante->usuario->apellidos ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Área / Carrera:</td>
                <td class="meta-val">{{ $pasante->area ?? 'Ingeniería en Sistemas y Redes Informáticas' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Informe:</td>
                <td class="meta-val">{{ $informe->nombre ?? 'Reporte de Horas' }} ({{ $informe->tipo === 'final' ? 'Informe Final' : 'Informe Mensual' }})</td>
            </tr>
            <tr>
                <td class="meta-label">Horas Reportadas:</td>
                <td class="meta-val">{{ $informe->horas ?? 0 }} horas</td>
            </tr>
            @if($informe->fecha_inicio && $informe->fecha_fin)
                <tr>
                    <td class="meta-label">Período:</td>
                    <td class="meta-val">Del {{ date('d/m/Y', strtotime($informe->fecha_inicio)) }} al {{ date('d/m/Y', strtotime($informe->fecha_fin)) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <p class="intro-text">
        Este apartado contiene la bitácora cronológica de las actividades realizadas correspondientes al presente ciclo. El bloque detalla de manera sistemática el avance del plan de trabajo por medio del cumplimiento de objetivos específicos, la ejecución de procesos operativos y la validación de resultados para asegurar la calidad en cada entrega institucional.
    </p>

    <table class="bitacora-table">
        <thead>
            <tr>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 25%;">Objetivo</th>
                <th style="width: 35%;">Actividades</th>
                <th style="width: 25%;">Logros y conclusiones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bitacora as $fila)
                <tr>
                    <td class="text-nowrap" style="width: 15%;">
                        {{ !empty($fila['fecha']) ? date('d/m/Y', strtotime($fila['fecha'])) : '' }}
                    </td>
                    <td style="width: 25%;">{{ $fila['objetivo'] ?? '' }}</td>
                    <td style="width: 35%;">{{ $fila['actividades'] ?? '' }}</td>
                    <td style="width: 25%;">{{ $fila['logros'] ?? '' }}</td>
                </tr>
            @empty
                <!-- Fallback si no hay bitácora y es un registro antiguo -->
                <tr>
                    <td colspan="4" style="text-align: center; color: #666; font-style: italic;">
                        Este informe no posee una bitácora detallada.
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top: 15px;">
                        <strong>Objetivos registrados:</strong><br>
                        {{ $informe->objetivos ?? 'No registrados' }}
                        <br><br>
                        <strong>Actividades registradas:</strong><br>
                        {{ $informe->actividades ?? 'No registradas' }}
                        <br><br>
                        <strong>Conclusiones registradas:</strong><br>
                        {{ $informe->conclusiones ?? 'No registradas' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($imagenesBase64))
        <div class="evidencias-section">
            <div class="evidencias-title">Evidencias / Anexos del Informe</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    @foreach($imagenesBase64 as $index => $imgBase64)
                        @if($index > 0 && $index % 2 == 0)
                            </tr><tr>
                        @endif
                        <td style="width: 50%; padding: 10px; text-align: center; border: none; vertical-align: middle;">
                            <img src="{{ $imgBase64 }}" style="max-width: 90%; max-height: 180px; border: 1px solid #cbd5e1; border-radius: 4px;" />
                            <div style="font-size: 8px; color: #666; margin-top: 5px;">Imagen de evidencia {{ $index + 1 }}</div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Informe Comparativo de Ciclos de Siembra</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informe Comparativo de Ciclos de Siembra</h1>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        <h3>Información del Análisis</h3>
        <p><strong>Ciclo 1:</strong> {{ $ciclo1 ?? 'No especificado' }}</p>
        <p><strong>Ciclo 2:</strong> {{ $ciclo2 ?? 'No especificado' }}</p>
        <p><strong>Métrica Analizada:</strong> {{ $metrica ?? 'No especificada' }}</p>
    </div>

    <div class="info-section">
        <h3>Resumen Comparativo</h3>
        <p>Este informe presenta un análisis comparativo entre dos ciclos de siembra basado en la métrica seleccionada.</p>
        <p>Los datos mostrados representan valores promedio diarios registrados durante el ciclo de cultivo.</p>
    </div>

    <div class="chart-container">
        <h3>Gráfico Comparativo</h3>
        <p>En una implementación completa, aquí se mostraría el gráfico comparativo de los ciclos.</p>
        <p>Los datos se obtienen de las lecturas de sensores registradas en la base de datos.</p>
    </div>

    <div class="info-section">
        <h3>Recomendaciones</h3>
        <ul>
            <li>Analizar las diferencias en los patrones de crecimiento entre ciclos</li>
            <li>Identificar factores ambientales que puedan haber influido en el rendimiento</li>
            <li>Considerar ajustes en prácticas de riego o fertilización basados en los resultados</li>
            <li>Documentar observaciones adicionales para futuras referencias</li>
        </ul>
    </div>

    <div class="footer">
        <p>Informe generado automáticamente por el Sistema BI Agrícola</p>
        <p>Este documento contiene información confidencial de la organización</p>
    </div>
</body>
</html>
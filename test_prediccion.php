<?php
require_once 'vendor/autoload.php';

// Configurar entorno Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CicloSiembra;
use App\Models\Valvula;
use App\Models\RiegoManual;
use App\Models\Temperatura;

try {
    echo "=== Test de Predicción de Agua ===\n\n";
    
    // 1. Obtener todos los ciclos FINALIZADOS (que tengan fecha fin)
    echo "1. Obteniendo ciclos finalizados...\n";
    $ciclosCerrados = CicloSiembra::whereNotNull('fechaFin')
        ->orderBy('fechaInicio', 'asc')
        ->take(10)
        ->get();
    
    echo "Ciclos encontrados: " . $ciclosCerrados->count() . "\n";
    
    foreach ($ciclosCerrados as $ciclo) {
        echo "Ciclo: " . $ciclo->cicloId . " - " . $ciclo->descripcion . " ({$ciclo->fechaInicio} a {$ciclo->fechaFin})\n";
    }
    
    echo "\n2. Calculando consumos por ciclo...\n";
    
    $labels = [];
    $dataHistorica = [];
    $sumaTotalConsumos = 0;
    $conteoCiclos = 0;
    
    $tipo = 'ambos'; // Simulando el parámetro
    
    foreach ($ciclosCerrados as $ciclo) {
        $consumoCiclo = 0;
        
        // Definir el rango de fechas de ese ciclo específico
        $inicio = $ciclo->fechaInicio;
        $fin = $ciclo->fechaFin;
        
        echo "Procesando ciclo: {$ciclo->cicloId} ({$inicio} a {$fin})\n";
        
        if ($tipo === 'valvula' || $tipo === 'ambos') {
            $valvulaSum = Valvula::whereBetween('fechaEncendido', [$inicio, $fin])->sum('volumen');
            echo "  - Consumo por valvula: {$valvulaSum}\n";
            $consumoCiclo += $valvulaSum;
        }
        
        if ($tipo === 'manual' || $tipo === 'ambos') {
            $manualSum = RiegoManual::whereBetween('fechaEncendido', [$inicio, $fin])->sum('volumen');
            echo "  - Consumo por riego manual: {$manualSum}\n";
            $consumoCiclo += $manualSum;
        }
        
        // Guardar datos para la gráfica
        $labels[] = $ciclo->descripcion ?? "Ciclo " . $ciclo->cicloId; 
        $dataHistorica[] = $consumoCiclo;
        
        echo "  - Consumo total ciclo: {$consumoCiclo}\n\n";
        
        $sumaTotalConsumos += $consumoCiclo;
        $conteoCiclos++;
    }
    
    // 3. Calcular el Promedio Histórico por Ciclo
    $promedioPorCiclo = $conteoCiclos > 0 ? ($sumaTotalConsumos / $conteoCiclos) : 0;
    echo "Promedio por ciclo: {$promedioPorCiclo}\n\n";
    
    // 4. Factor de Ajuste Térmico
    echo "4. Obteniendo última temperatura...\n";
    $ultimaTemperatura = Temperatura::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
    $temperaturaActual = $ultimaTemperatura ? $ultimaTemperatura->temperatura : 25;
    echo "Temperatura actual: {$temperaturaActual}°C\n\n";
    
    // Fórmula
    $factorAjuste = 1 + (($temperaturaActual - 25) / 100);
    $prediccionSiguienteCiclo = $promedioPorCiclo * $factorAjuste;
    
    echo "Factor de ajuste: {$factorAjuste}\n";
    echo "Predicción siguiente ciclo: {$prediccionSiguienteCiclo}\n\n";
    
    // Resultado final
    echo "=== RESULTADO FINAL ===\n";
    echo "Labels: " . json_encode($labels) . "\n";
    echo "Data: " . json_encode($dataHistorica) . "\n";
    echo "Predicción: {$prediccionSiguienteCiclo}\n";
    echo "Temperatura: {$temperaturaActual}\n";
    echo "Promedio histórico: {$promedioPorCiclo}\n";
    echo "Mensaje: Basado en {$conteoCiclos} ciclos anteriores, se estiman " . number_format($prediccionSiguienteCiclo, 2) . " Litros para el siguiente ciclo completo.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
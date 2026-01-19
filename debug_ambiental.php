<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Temperatura;
use App\Models\CicloSiembra;

echo "=== DEBUG DE DATOS AMBIENTALES ===\n\n";

// Check temperatura table
echo "Registros en tabla temperatura:\n";
$tempCount = Temperatura::count();
echo "Total registros: $tempCount\n";

if ($tempCount > 0) {
    $sample = Temperatura::orderBy('fecha', 'desc')->limit(3)->get();
    foreach($sample as $temp) {
        echo "- Fecha: {$temp->fecha} {$temp->hora} | Temp: {$temp->temperatura}°C | Hum: {$temp->humedad}%\n";
    }
    
    // Check date range
    $minDate = Temperatura::min('fecha');
    $maxDate = Temperatura::max('fecha');
    echo "\nRango de fechas: $minDate a $maxDate\n";
} else {
    echo "No hay registros en la tabla temperatura\n";
}

echo "\n=== VERIFICACIÓN DE CICLOS ===\n";
$ciclos = CicloSiembra::whereNotNull('fechaFin')->get();
foreach($ciclos as $ciclo) {
    echo "- {$ciclo->descripcion}: {$ciclo->fechaInicio} a {$ciclo->fechaFin}\n";
}

echo "\n=== FIN DEBUG ===\n";
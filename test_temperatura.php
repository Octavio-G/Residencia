<?php
require_once 'vendor/autoload.php';

// Configurar entorno Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Temperatura;

try {
    $ultimaTemperatura = Temperatura::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
    echo "Última temperatura encontrada:\n";
    if ($ultimaTemperatura) {
        echo "ID: " . $ultimaTemperatura->idTemperatura . "\n";
        echo "Temperatura: " . $ultimaTemperatura->temperatura . "\n";
        echo "Fecha: " . $ultimaTemperatura->fecha . "\n";
        echo "Hora: " . $ultimaTemperatura->hora . "\n";
    } else {
        echo "No se encontraron registros\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
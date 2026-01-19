<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RiegoManual;
use Carbon\Carbon;

echo "=== TEST DE CONSULTA DIRECTA ===\n\n";

// Test with one of the IDs we know exists
$cultivoId = '739e342b-9d6a-4dc5-a76d-01db4fdf4b15';

echo "Buscando registros para cultivoId: $cultivoId\n";

// Get some sample data
$sampleRecords = RiegoManual::where('cultivoId', $cultivoId)
    ->orderBy('fechaEncendido', 'desc')
    ->limit(5)
    ->get();

echo "Registros encontrados: " . $sampleRecords->count() . "\n\n";

foreach($sampleRecords as $record) {
    echo "Fecha: {$record->fechaEncendido} | Volumen: {$record->volumen}L\n";
}

// Test sum
$total = RiegoManual::where('cultivoId', $cultivoId)->sum('volumen');
echo "\nTotal volumen: {$total}L\n";

echo "\n=== FIN TEST ===\n";
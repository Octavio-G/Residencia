<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Valvula;
use App\Models\RiegoManual;
use App\Models\CicloSiembra;

echo "=== DEBUG DE DATOS DE RIEGO ===\n\n";

// Check available cultivoIds in Valvula table
echo "IDs de cultivo en tabla valvula:\n";
$valvulaIds = Valvula::distinct()->pluck('cultivoId')->toArray();
foreach($valvulaIds as $id) {
    echo "- $id\n";
}

echo "\nIDs de cultivo en tabla riegomanual:\n";
$riegoIds = RiegoManual::distinct()->pluck('cultivoId')->toArray();
foreach($riegoIds as $id) {
    echo "- $id\n";
}

echo "\nCiclos de siembra disponibles:\n";
$ciclos = CicloSiembra::all(['cicloId', 'descripcion', 'fechaInicio', 'fechaFin']);
foreach($ciclos as $ciclo) {
    echo "- {$ciclo->descripcion}: {$ciclo->fechaInicio} a {$ciclo->fechaFin}\n";
}

echo "\n=== FIN DEBUG ===\n";
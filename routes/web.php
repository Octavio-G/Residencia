<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrediccionController;
use App\Http\Controllers\ComparativaController;
use App\Http\Controllers\IndiceSecadoController;
use App\Http\Controllers\PrediccionAguaController;


Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas de registro
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Ruta para verificar estructura de tablas (sin autenticación)
Route::get('/debug-estructura', function() {
    try {
        $ciclo = App\Models\CicloSiembra::first();
        $cultivo = App\Models\Cultivo::first();
        $cama1 = App\Models\CamaSiembra::first();
        $cama2 = App\Models\Cama2::first();
        
        // Verificar si hay campo 'cultivo' en cama1 y cama2
        $cama1WithCultivo = null;
        $cama2WithCultivo = null;
        
        if ($cama1) {
            $cama1Cultivo = $cama1->cultivo ?? 'no_existe';
            $cama1WithCultivo = [
                'idCama1' => $cama1->idCama1,
                'humedad' => $cama1->humedad,
                'fecha' => $cama1->fecha,
                'hora' => $cama1->hora,
                'cultivo' => $cama1Cultivo,
                'fillable' => (new App\Models\CamaSiembra)->getFillable()
            ];
        }
        
        if ($cama2) {
            $cama2Cultivo = $cama2->cultivo ?? 'no_existe';
            $cama2WithCultivo = [
                'idCama2' => $cama2->idCama2,
                'humedad' => $cama2->humedad,
                'fecha' => $cama2->fecha,
                'hora' => $cama2->hora,
                'cultivo' => $cama2Cultivo,
                'fillable' => (new App\Models\Cama2)->getFillable()
            ];
        }
        
        return response()->json([
            'ciclo' => $ciclo ? $ciclo->toArray() : null,
            'cultivo' => $cultivo ? $cultivo->toArray() : null,
            'cama1' => $cama1WithCultivo,
            'cama2' => $cama2WithCultivo,
        ]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

// Rutas protegidas para el módulo BI (requieren autenticación)
Route::middleware('auth')->group(function () {
    Route::prefix('bi')->group(function () {
        Route::get('/', [BiController::class, 'index'])->name('bi.dashboard');
        Route::get('/indicador-salud', [BiController::class, 'indicadorSalud'])->name('bi.indicador-salud');
        Route::get('/indice-secado', [IndiceSecadoController::class, 'index'])->name('bi.indice-secado');
        Route::get('/indice-secado/calcular', [IndiceSecadoController::class, 'calcularIndiceSecado']);

        Route::get('/historial-lecturas', [BiController::class, 'historialLecturas'])->name('bi.historial-lecturas');
        // Route::get('/prediccion-secado', [PrediccionController::class, 'prediccionSecado'])->name('bi.prediccion-secado');
        Route::get('/ciclos-siembra', [BiController::class, 'ciclosSiembra'])->name('bi.ciclos-siembra');
        Route::post('/volumen-agua-ciclo', [BiController::class, 'volumenAguaCiclo'])->name('bi.volumen-agua-ciclo');
        Route::post('/volumen-agua-valvula-ciclo', [BiController::class, 'volumenAguaValvulaCiclo'])->name('bi.volumen-agua-valvula-ciclo');
        Route::post('/volumen-agua-riego-manual-ciclo', [BiController::class, 'volumenAguaRiegoManualCiclo'])->name('bi.volumen-agua-riego-manual-ciclo');
        Route::post('/test-volumen-panel', [BiController::class, 'testVolumenPanel'])->name('bi.test-volumen-panel');
        Route::post('/datos-ciclo', [BiController::class, 'datosCiclo'])->name('bi.datos-ciclo');
        Route::post('/comparar-ciclos', [BiController::class, 'compararCiclos'])->name('bi.comparar-ciclos');
        Route::get('/exportar-pdf', [BiController::class, 'exportarPdf'])->name('bi.exportar-pdf');
        
        // Rutas para comparativa histórica
        Route::get('/comparativa', [ComparativaController::class, 'index'])->name('bi.comparativa');
        Route::get('/comparativa/ciclos-finalizados', [ComparativaController::class, 'getCiclosFinalizados']);
        Route::post('/comparativa/comparar', [ComparativaController::class, 'compararCiclos']);
        Route::post('/comparativa/totales', [ComparativaController::class, 'getTotalesCiclos']);
        
        // Ruta para verificar estructura de tablas
        Route::get('/debug-estructura', function() {
            try {
                $ciclo = App\Models\CicloSiembra::first();
                $cultivo = App\Models\Cultivo::first();
                $cama1 = App\Models\CamaSiembra::first();
                $cama2 = App\Models\Cama2::first();
                
                // Verificar si hay campo 'cultivo' en cama1 y cama2
                $cama1WithCultivo = null;
                $cama2WithCultivo = null;
                
                if ($cama1) {
                    $cama1Cultivo = $cama1->cultivo ?? 'no_existe';
                    $cama1WithCultivo = [
                        'idCama1' => $cama1->idCama1,
                        'humedad' => $cama1->humedad,
                        'fecha' => $cama1->fecha,
                        'hora' => $cama1->hora,
                        'cultivo' => $cama1Cultivo,
                        'fillable' => (new App\Models\CamaSiembra)->getFillable()
                    ];
                }
                
                if ($cama2) {
                    $cama2Cultivo = $cama2->cultivo ?? 'no_existe';
                    $cama2WithCultivo = [
                        'idCama2' => $cama2->idCama2,
                        'humedad' => $cama2->humedad,
                        'fecha' => $cama2->fecha,
                        'hora' => $cama2->hora,
                        'cultivo' => $cama2Cultivo,
                        'fillable' => (new App\Models\Cama2)->getFillable()
                    ];
                }
                
                return response()->json([
                    'ciclo' => $ciclo ? $ciclo->toArray() : null,
                    'cultivo' => $cultivo ? $cultivo->toArray() : null,
                    'cama1' => $cama1WithCultivo,
                    'cama2' => $cama2WithCultivo,
                ]);
            } catch (Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        });
        
        // Rutas para predicciones
        Route::get('/predicciones', [PrediccionController::class, 'index']);
        Route::get('/prediccion/ciclos', [PrediccionController::class, 'getCiclos']);
        Route::get('/prediccion/opciones', [PrediccionController::class, 'getPrediccionesDisponibles']);
        Route::post('/prediccion/calcular', [PrediccionController::class, 'calcularPrediccion']);
        
        // Ruta para vista de predicción de agua
        Route::get('/prediccion-agua-view', [PrediccionAguaController::class, 'index']);
        // Ruta para la API de predicción (AJAX)
        Route::get('/prediccion-agua', [PrediccionAguaController::class, 'predecir']);
        

    });
});
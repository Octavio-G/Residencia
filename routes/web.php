<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrediccionController;

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

// Rutas protegidas para el módulo BI (requieren autenticación)
Route::middleware('auth')->group(function () {
    Route::prefix('bi')->group(function () {
        Route::get('/', [BiController::class, 'index'])->name('bi.dashboard');
        Route::get('/indicador-salud', [BiController::class, 'indicadorSalud'])->name('bi.indicador-salud');
        Route::get('/alerta-secado', [BiController::class, 'alertaSecado'])->name('bi.alerta-secado');
        Route::get('/historial-lecturas', [BiController::class, 'historialLecturas'])->name('bi.historial-lecturas');
        Route::get('/prediccion-secado', [PrediccionController::class, 'prediccionSecado'])->name('bi.prediccion-secado');
        Route::get('/ciclos-siembra', [BiController::class, 'ciclosSiembra'])->name('bi.ciclos-siembra');
        Route::post('/volumen-agua-ciclo', [BiController::class, 'volumenAguaCiclo'])->name('bi.volumen-agua-ciclo');
        Route::post('/datos-ciclo', [BiController::class, 'datosCiclo'])->name('bi.datos-ciclo');
        Route::post('/comparar-ciclos', [BiController::class, 'compararCiclos'])->name('bi.comparar-ciclos');
        Route::get('/exportar-pdf', [BiController::class, 'exportarPdf'])->name('bi.exportar-pdf');
    });
});
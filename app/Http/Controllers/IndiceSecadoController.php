<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CamaSiembra;
use App\Models\Cama2;
use App\Models\Temperatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IndiceSecadoController extends Controller
{
    public function index()
    {
        return view('bi.indice_secado');
    }

    public function calcularIndiceSecado()
    {
        // Obtener los datos más recientes para ambas camas
        $cama1 = $this->calcularDatosCama(CamaSiembra::class, 'Cama 1');
        $cama2 = $this->calcularDatosCama(Cama2::class, 'Cama 2');

        return response()->json([
            'cama1' => $cama1,
            'cama2' => $cama2
        ]);
    }

    private function calcularDatosCama($modelo, $nombreCama)
    {
        // Obtener las últimas 10 lecturas de humedad para mostrar en la gráfica
        $lecturas = $modelo::orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lectura) {
                return [
                    'fecha' => $lectura->fecha,
                    'hora' => $lectura->hora,
                    'humedad' => $lectura->humedad
                ];
            })
            ->reverse() // Para mostrar en orden cronológico
            ->values();

        // Obtener la lectura más reciente
        $lecturaReciente = $modelo::orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();

        // Obtener la temperatura más reciente cercana a la fecha de la lectura
        $temperatura = 0;
        if ($lecturaReciente) {
            $temperatura = $this->obtenerTemperaturaCercana($lecturaReciente->fecha);
        }

        // Calcular el tiempo estimado hasta que la humedad alcance niveles críticos
        // Suponiendo una tasa de secado promedio (esto es una simplificación)
        $humedadActual = $lecturaReciente ? $lecturaReciente->humedad : 0;
        $minutosRestantes = $this->calcularMinutosHastaSecado($humedadActual);

        // Determinar mensaje de estado
        $mensajeEstado = $this->obtenerMensajeEstado($humedadActual, $minutosRestantes);

        return [
            'nombre' => $nombreCama,
            'cultivo' => 'Tomate', // Placeholder - debería obtenerse del modelo real
            'humedad_actual' => $humedadActual,
            'temperatura_actual' => $temperatura,
            'tiempo_restante' => [
                'horas' => intval($minutosRestantes / 60),
                'minutos' => $minutosRestantes % 60
            ],
            'mensaje_estado' => $mensajeEstado,
            'lecturas_historial' => $lecturas
        ];
    }

    private function obtenerTemperaturaCercana($fecha)
    {
        // Buscar temperatura registrada cerca de la fecha especificada
        $temperatura = Temperatura::whereDate('fecha', $fecha)
            ->orderBy('hora', 'desc')
            ->first();

        return $temperatura ? $temperatura->temperatura : 25; // Valor por defecto
    }

    private function calcularMinutosHastaSecado($humedadActual)
    {
        // Simplificación: asumimos que la humedad disminuye a una tasa constante
        // En una implementación real, esto dependería de múltiples factores
        if ($humedadActual <= 30) {
            return 0; // Ya está en nivel crítico
        }

        // Asumiendo que la humedad disminuye 1% cada 2 horas en condiciones normales
        $tasaSecado = 0.5; // % por hora
        $humedadADisminuir = $humedadActual - 30; // Hasta el nivel crítico
        $horasHastaSecado = $humedadADisminuir / $tasaSecado;

        return intval($horasHastaSecado * 60); // Convertir a minutos
    }

    private function obtenerMensajeEstado($humedadActual, $minutosRestantes)
    {
        if ($humedadActual <= 30) {
            return "CRÍTICO: Humedad por debajo del 30%";
        } elseif ($humedadActual <= 50) {
            return "URGENTE: Riego recomendado pronto";
        } else {
            return "NORMAL: Niveles de humedad adecuados";
        }
    }
}
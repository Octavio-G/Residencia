<?php

namespace App\Http\Controllers;

use App\Models\CamaSiembra;
use App\Models\Cama2;
use App\Models\Temperatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndiceSecadoController extends Controller
{
    public function index()
    {
        return view('bi.indice_secado');
    }

    public function calcularIndiceSecado()
    {
        try {
            // Obtener datos de ambas camas
            $cama1 = $this->calcularDatosCama(CamaSiembra::class, 'cama1');
            $cama2 = $this->calcularDatosCama(Cama2::class, 'cama2');

            return response()->json([
                'cama1' => $cama1,
                'cama2' => $cama2
            ]);
        } catch (\Exception $e) {
            Log::error('Error en calcularIndiceSecado: ' . $e->getMessage());
            return response()->json(['error' => 'Error al calcular el índice de secado'], 500);
        }
    }

    private function calcularDatosCama($modelo, $nombreCama)
    {
        $umbralCritico = 30.0;
        
        // Obtener las últimas 15 lecturas para el historial
        $lecturas = $modelo::orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->limit(15)
            ->get(['humedad', 'fecha', 'hora'])
            ->map(function ($lectura) {
                // Formatear la fecha para que sea más legible
                $fechaFormateada = $lectura->fecha;
                if (is_string($lectura->fecha)) {
                    $fechaCarbon = \Carbon\Carbon::parse($lectura->fecha);
                    $fechaFormateada = $fechaCarbon->format('Y-m-d');
                }
                return [
                    'humedad' => $lectura->humedad,
                    'fecha' => $fechaFormateada,
                    'hora' => $lectura->hora
                ];
            })
            ->reverse() // Invertir para tener las más recientes al final
            ->toArray();

        if (empty($lecturas)) {
            return [
                'nombre' => ucfirst($nombreCama),
                'tiempo_restante' => 0,
                'humedad_actual' => 0,
                'temperatura_actual' => 0,
                'lecturas_historial' => [],
                'cultivo' => 'No asignado',
                'mensaje_estado' => 'Sin datos disponibles'
            ];
        }

        // Obtener humedad actual (última lectura)
        $ultimaLectura = end($lecturas);
        $humedadActual = $ultimaLectura['humedad'];
        $fechaActual = $ultimaLectura['fecha'];

        // Obtener temperatura actual más cercana a la fecha de la última lectura
        $temperaturaActual = $this->obtenerTemperaturaCercana($fechaActual);

        // Obtener las últimas 5 lecturas para calcular la velocidad
        $ultimas5 = array_slice($lecturas, -5);
        
        if (count($ultimas5) < 2) {
            $velocidad = 0;
        } else {
            $velocidad = 0;
            $anterior = null;
            
            foreach ($ultimas5 as $lectura) {
                if ($anterior !== null) {
                    // Calcular diferencia de humedad y tiempo
                    $diferenciaHumedad = $anterior['humedad'] - $lectura['humedad'];
                    
                    // Convertir fechas a timestamps para calcular diferencia en minutos
                    $fechaAnterior = strtotime($anterior['fecha'] . ' ' . $anterior['hora']);
                    $fechaActualLectura = strtotime($lectura['fecha'] . ' ' . $lectura['hora']);
                    
                    if ($fechaActualLectura > $fechaAnterior) {
                        $diferenciaMinutos = ($fechaActualLectura - $fechaAnterior) / 60;
                        if ($diferenciaMinutos > 0) {
                            $velocidad += $diferenciaHumedad / $diferenciaMinutos;
                        }
                    }
                }
                $anterior = $lectura;
            }
            
            $velocidad = $velocidad / (count($ultimas5) - 1);
        }

        // Ajuste térmico
        if ($temperaturaActual > 30) {
            $velocidad *= 1.2;
        }

        // Cálculo final
        if ($humedadActual <= $umbralCritico || $velocidad <= 0) {
            $minutosRestantes = 0;
        } else {
            $minutosRestantes = ($humedadActual - $umbralCritico) / $velocidad;
        }

        // Asegurar que no sea negativo
        $minutosRestantes = max(0, $minutosRestantes);

        // Obtener información del cultivo
        $ultimaCama = $modelo::orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();
        
        $cultivo = $ultimaCama ? $ultimaCama->cultivo : 'No asignado';

        // Formatear tiempo restante
        $horas = floor($minutosRestantes / 60);
        $minutos = round($minutosRestantes % 60);

        // Preparar datos para historial
        $lecturasHistorial = [];
        foreach ($lecturas as $lectura) {
            $lecturasHistorial[] = [
                'fecha' => $lectura['fecha'],
                'hora' => $lectura['hora'],
                'humedad' => $lectura['humedad']
            ];
        }

        return [
            'nombre' => ucfirst($nombreCama),
            'tiempo_restante' => [
                'horas' => $horas,
                'minutos' => $minutos
            ],
            'humedad_actual' => $humedadActual,
            'temperatura_actual' => $temperaturaActual,
            'lecturas_historial' => $lecturasHistorial,
            'cultivo' => $cultivo,
            'mensaje_estado' => $this->obtenerMensajeEstado($humedadActual, $minutosRestantes)
        ];
    }

    private function obtenerTemperaturaCercana($fecha)
    {
        // Asegurarse de que la fecha esté en formato correcto
        if (strpos($fecha, 'T') !== false) {
            $fecha = substr($fecha, 0, 10); // Extraer solo la parte de la fecha
        }

        // Buscar temperatura más cercana a la fecha dada
        $temperatura = Temperatura::whereDate('fecha', $fecha)
            ->orderBy('hora', 'desc')
            ->first();

        if ($temperatura) {
            return $temperatura->temperatura ?? 0;
        }

        // Si no hay temperatura para esa fecha, buscar en días cercanos
        $temperatura = Temperatura::whereDate('fecha', '<=', $fecha)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();

        return $temperatura ? $temperatura->temperatura : 0;
    }

    private function obtenerMensajeEstado($humedadActual, $minutosRestantes)
    {
        if ($humedadActual <= 30) {
            return '¡ESTRÉS CRÍTICO!';
        } elseif ($minutosRestantes < 120) { // Menos de 2 horas
            return '¡ATENCIÓN URGENTE!';
        } else {
            return 'ESTADO NORMAL';
        }
    }
}
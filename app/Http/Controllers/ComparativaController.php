<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CicloSiembra;
use App\Models\Cultivo;
use App\Models\CamaSiembra;  // Esta tabla es cama1
use App\Models\Cama2;
use App\Models\Valvula;
use App\Models\RiegoManual;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ComparativaController extends Controller
{
    public function index()
    {
        return view('bi.comparativa_historica');
    }

    /**
     * Obtener ciclos finalizados para los dropdowns
     */
    public function getCiclosFinalizados()
    {
        $ciclos = CicloSiembra::whereNotNull('fechaFin')
            ->whereNotNull('fechaInicio')
            ->orderBy('fechaInicio', 'desc')
            ->get(['cicloId', 'descripcion', 'fechaInicio', 'fechaFin']);

        $options = '<option value="">Seleccione un ciclo</option>';
        foreach ($ciclos as $ciclo) {
            $options .= '<option value="' . $ciclo->cicloId . '">' . $ciclo->descripcion . ' (' . $ciclo->fechaInicio . ' - ' . $ciclo->fechaFin . ')</option>';
        }

        return response()->json(['options' => $options]);
    }

    /**
     * Comparar dos ciclos de siembra
     */
    public function compararCiclos(Request $request)
    {
        $cicloAId = $request->input('ciclo_a');
        $cicloBId = $request->input('ciclo_b');
        $tipoGrafica = $request->input('tipo_grafica', 'lineal');
        $tipoDato = $request->input('tipo_dato', 'humedad');
        $tipoRiego = $request->input('tipo_riego', 'ambos'); // manual, valvula, ambos

        if (!$cicloAId || !$cicloBId) {
            return response()->json(['error' => 'Debe seleccionar ambos ciclos'], 400);
        }

        $cicloA = CicloSiembra::where('cicloId', $cicloAId)->first();
        $cicloB = CicloSiembra::where('cicloId', $cicloBId)->first();

        if (!$cicloA || !$cicloB) {
            return response()->json(['error' => 'Ciclos no encontrados'], 404);
        }

        // Obtener datos normalizados para cada ciclo
        $datosCicloA = $this->obtenerDatosNormalizados($cicloA, $tipoDato, $tipoRiego);
        $datosCicloB = $this->obtenerDatosNormalizados($cicloB, $tipoDato, $tipoRiego);

        return response()->json([
            'ciclo_a' => [
                'nombre' => $cicloA->descripcion,
                'id' => $cicloA->cicloId,
                'datos' => $datosCicloA
            ],
            'ciclo_b' => [
                'nombre' => $cicloB->descripcion,
                'id' => $cicloB->cicloId,
                'datos' => $datosCicloB
            ],
            'config' => [
                'tipo_grafica' => $tipoGrafica,
                'tipo_dato' => $tipoDato,
                'tipo_riego' => $tipoRiego
            ]
        ]);
    }

    /**
     * Obtener datos normalizados para un ciclo específico
     */
    private function obtenerDatosNormalizados($ciclo, $tipoDato, $tipoRiego)
    {
        // Obtener IDs de cultivos asociados al ciclo
        $cultivoIds = Cultivo::where('cicloId', $ciclo->cicloId)->pluck('cultivoId');

        // Debug: Verificar si hay cultivoIds
        Log::info('CultivoIds para ciclo ' . $ciclo->cicloId . ': ' . $cultivoIds->implode(','));

        // Normalizar fechas para el eje X (Día del Ciclo)
        $fechaInicio = Carbon::parse($ciclo->fechaInicio);
        $fechaFin = Carbon::parse($ciclo->fechaFin);
        $duracionCiclo = $fechaInicio->diffInDays($fechaFin) + 1;

        $datos = [];

        if ($tipoDato === 'humedad' || $tipoDato === 'humedad_cama1' || $tipoDato === 'humedad_cama2') {
            // Para humedad, usamos las fechas del ciclo para filtrar datos
            $fechaActual = clone $fechaInicio;
            $dia = 1;
            
            while ($fechaActual->lte($fechaFin)) {
                $fechaStr = $fechaActual->format('Y-m-d');
                
                $valorHumedad = 0;
                
                if ($tipoDato === 'humedad' || $tipoDato === 'humedad_cama1') {
                    // Obtener humedad promedio de cama1 para esta fecha
                    $humedadCama1 = CamaSiembra::whereDate('fecha', $fechaStr)
                        ->avg('humedad');
                }
                
                if ($tipoDato === 'humedad' || $tipoDato === 'humedad_cama2') {
                    // Obtener humedad promedio de cama2 para esta fecha
                    $humedadCama2 = Cama2::whereDate('fecha', $fechaStr)
                        ->avg('humedad');
                }
                
                if ($tipoDato === 'humedad') {
                    // Promedio de ambas camas
                    $totalHumedad = 0;
                    $count = 0;
                    
                    if ($humedadCama1 !== null) {
                        $totalHumedad += $humedadCama1;
                        $count++;
                    }
                    
                    if ($humedadCama2 !== null) {
                        $totalHumedad += $humedadCama2;
                        $count++;
                    }
                    
                    $valorHumedad = $count > 0 ? $totalHumedad / $count : 0;
                } elseif ($tipoDato === 'humedad_cama1') {
                    $valorHumedad = $humedadCama1 !== null ? $humedadCama1 : 0;
                } elseif ($tipoDato === 'humedad_cama2') {
                    $valorHumedad = $humedadCama2 !== null ? $humedadCama2 : 0;
                }

                $datos[] = [
                    'dia' => $dia,
                    'valor' => round($valorHumedad, 2),
                    'fecha_registro' => $fechaStr
                ];

                $fechaActual->addDay();
                $dia++;
            }
        } elseif ($tipoDato === 'consumo_agua') {
            // Obtener datos de consumo de agua (diario)
            $fechaActual = clone $fechaInicio;
            $dia = 1;
            while ($fechaActual->lte($fechaFin)) {
                $fechaStr = $fechaActual->format('Y-m-d');
                
                // Calcular consumo de agua para este día según el tipo seleccionado
                $volumen = 0;
                
                if ($tipoRiego === 'manual' || $tipoRiego === 'ambos') {
                    $volumen += RiegoManual::whereDate('fechaEncendido', $fechaStr)
                        ->sum('volumen');
                }
                
                if ($tipoRiego === 'valvula' || $tipoRiego === 'ambos') {
                    $volumen += Valvula::whereDate('fechaEncendido', $fechaStr)
                        ->sum('volumen');
                }

                $datos[] = [
                    'dia' => $dia,
                    'valor' => $volumen,
                    'fecha_registro' => $fechaStr
                ];

                $fechaActual->addDay();
                $dia++;
            }
        }

        // Si el ciclo tiene menos de 40 días, rellenar con 0 o null
        if (count($datos) < 40) {
            $diasFaltantes = 40 - count($datos);
            for ($i = 0; $i < $diasFaltantes; $i++) {
                $datos[] = [
                    'dia' => count($datos) + 1 + $i,
                    'valor' => 0,
                    'fecha_registro' => null
                ];
            }
        }

        return $datos;
    }

    /**
     * Obtener totales para gráficas de pastel
     */
    public function getTotalesCiclos(Request $request)
    {
        $cicloAId = $request->input('ciclo_a');
        $cicloBId = $request->input('ciclo_b');

        if (!$cicloAId || !$cicloBId) {
            return response()->json(['error' => 'Debe seleccionar ambos ciclos'], 400);
        }

        $cicloA = CicloSiembra::where('cicloId', $cicloAId)->first();
        $cicloB = CicloSiembra::where('cicloId', $cicloBId)->first();

        if (!$cicloA || !$cicloB) {
            return response()->json(['error' => 'Ciclos no encontrados'], 404);
        }

        // Calcular totales para ciclo A
        $totalManualA = RiegoManual::whereBetween('fechaEncendido', [$cicloA->fechaInicio, $cicloA->fechaFin])
            ->sum('volumen');

        $totalValvulaA = Valvula::whereBetween('fechaEncendido', [$cicloA->fechaInicio, $cicloA->fechaFin])
            ->sum('volumen');

        // Calcular totales para ciclo B
        $totalManualB = RiegoManual::whereBetween('fechaEncendido', [$cicloB->fechaInicio, $cicloB->fechaFin])
            ->sum('volumen');

        $totalValvulaB = Valvula::whereBetween('fechaEncendido', [$cicloB->fechaInicio, $cicloB->fechaFin])
            ->sum('volumen');

        return response()->json([
            'ciclo_a' => [
                'nombre' => $cicloA->descripcion,
                'totales' => [
                    'manual' => $totalManualA,
                    'valvula' => $totalValvulaA,
                    'total' => $totalManualA + $totalValvulaA
                ]
            ],
            'ciclo_b' => [
                'nombre' => $cicloB->descripcion,
                'totales' => [
                    'manual' => $totalManualB,
                    'valvula' => $totalValvulaB,
                    'total' => $totalManualB + $totalValvulaB
                ]
            ]
        ]);
    }
}
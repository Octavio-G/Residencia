<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CicloSiembra;
use App\Models\RiegoManual;
use App\Models\Valvula;
use App\Models\CamaSiembra;
use App\Models\Cama2;
use Illuminate\Support\Facades\DB;

class PrediccionController extends Controller
{
    public function index()
    {
        return view('bi.predicciones');
    }

    public function getCiclos()
    {
        try {
            $ciclos = CicloSiembra::where('estado', 'activo')
                ->orWhere('estado', 'completado')
                ->orderBy('fecha_inicio', 'desc')
                ->get(['id', 'descripcion', 'fecha_inicio', 'fecha_fin', 'estado']);

            $options = '';
            foreach ($ciclos as $ciclo) {
                $options .= '<option value="' . $ciclo->id . '">' . $ciclo->descripcion . ' (' . $ciclo->fecha_inicio . ' - ' . $ciclo->fecha_fin . ')</option>';
            }

            return response()->json([
                'success' => true,
                'options' => $options
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPrediccionesDisponibles()
    {
        $predicciones = [
            [
                'id' => 'consumo_agua',
                'nombre' => 'Consumo de Agua',
                'descripcion' => 'Predicción del consumo de agua basado en datos históricos y tendencias actuales',
                'icono' => 'fa-tint'
            ],
            [
                'id' => 'humedad',
                'nombre' => 'Humedad',
                'descripcion' => 'Predicción de niveles de humedad en las camas de siembra',
                'icono' => 'fa-water'
            ],
            [
                'id' => 'rendimiento',
                'nombre' => 'Rendimiento',
                'descripcion' => 'Predicción del rendimiento del cultivo basado en condiciones actuales',
                'icono' => 'fa-chart-line'
            ]
        ];

        return response()->json([
            'success' => true,
            'predicciones' => $predicciones
        ]);
    }

    public function calcularPrediccion(Request $request)
    {
        $tipo = $request->input('tipo_prediccion');
        $cicloId = $request->input('ciclo_id');

        switch ($tipo) {
            case 'consumo_agua':
                return $this->calcularPrediccionConsumoAgua($cicloId);
            case 'humedad':
                return $this->calcularPrediccionHumedad($cicloId);
            case 'rendimiento':
                return $this->calcularPrediccionRendimiento($cicloId);
            default:
                return response()->json([
                    'success' => false,
                    'error' => 'Tipo de predicción no válido'
                ], 400);
        }
    }

    private function calcularPrediccionConsumoAgua($cicloId)
    {
        try {
            // Obtener datos históricos de consumo de agua para el ciclo
            $riegoManual = RiegoManual::where('ciclo_siembra_id', $cicloId)
                ->select(DB::raw('DATE(fecha) as fecha, SUM(volumen_agua) as volumen'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get();

            $valvula = Valvula::where('ciclo_siembra_id', $cicloId)
                ->select(DB::raw('DATE(fecha) as fecha, SUM(volumen_agua) as volumen'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get();

            // Combinar datos de ambos tipos de riego
            $datos = [];
            $fechas = collect($riegoManual)->pluck('fecha')->merge(collect($valvula)->pluck('fecha'))->unique()->sort();

            foreach ($fechas as $fecha) {
                $volumenManual = $riegoManual->firstWhere('fecha', $fecha);
                $volumenValvula = $valvula->firstWhere('fecha', $fecha);
                
                $datos[] = [
                    'fecha' => $fecha,
                    'consumo_total' => ($volumenManual ? $volumenManual->volumen : 0) + ($volumenValvula ? $volumenValvula->volumen : 0)
                ];
            }

            // Calcular promedio y tendencia para predicción
            $totalDias = count($datos);
            $promedioDiario = $totalDias > 0 ? array_sum(array_column($datos, 'consumo_total')) / $totalDias : 0;

            // Predicción simple basada en promedio
            $prediccion = [
                'prediccion' => round($promedioDiario * 7, 2), // Predicción para los próximos 7 días
                'unidad' => 'litros',
                'periodo' => 'próximos 7 días',
                'historico' => $datos,
                'promedio_diario' => round($promedioDiario, 2)
            ];

            return response()->json([
                'success' => true,
                'tipo' => 'consumo_agua',
                'datos' => $prediccion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calcularPrediccionHumedad($cicloId)
    {
        try {
            // Obtener datos históricos de humedad para el ciclo
            $cama1 = CamaSiembra::where('ciclo_siembra_id', $cicloId)
                ->select(DB::raw('DATE(fecha) as fecha, AVG(humedad) as promedio_humedad'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get();

            $cama2 = Cama2::where('ciclo_siembra_id', $cicloId)
                ->select(DB::raw('DATE(fecha) as fecha, AVG(humedad) as promedio_humedad'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get();

            // Calcular promedios y tendencias
            $totalDiasCama1 = count($cama1);
            $promedioCama1 = $totalDiasCama1 > 0 ? array_sum(array_column($cama1->toArray(), 'promedio_humedad')) / $totalDiasCama1 : 0;

            $totalDiasCama2 = count($cama2);
            $promedioCama2 = $totalDiasCama2 > 0 ? array_sum(array_column($cama2->toArray(), 'promedio_humedad')) / $totalDiasCama2 : 0;

            $prediccion = [
                'cama1' => [
                    'prediccion' => round($promedioCama1, 2),
                    'unidad' => '%',
                    'historico' => $cama1->toArray()
                ],
                'cama2' => [
                    'prediccion' => round($promedioCama2, 2),
                    'unidad' => '%',
                    'historico' => $cama2->toArray()
                ],
                'promedio_general' => round(($promedioCama1 + $promedioCama2) / 2, 2)
            ];

            return response()->json([
                'success' => true,
                'tipo' => 'humedad',
                'datos' => $prediccion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calcularPrediccionRendimiento($cicloId)
    {
        try {
            // Obtener información del ciclo para calcular rendimiento
            $ciclo = CicloSiembra::find($cicloId);

            if (!$ciclo) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ciclo no encontrado'
                ], 404);
            }

            // Calcular días restantes o transcurridos
            $fechaInicio = \Carbon\Carbon::parse($ciclo->fecha_inicio);
            $fechaFin = \Carbon\Carbon::parse($ciclo->fecha_fin);
            $fechaActual = \Carbon\Carbon::now();

            $diasTotales = $fechaInicio->diffInDays($fechaFin);
            $diasTranscurridos = $fechaInicio->diffInDays($fechaActual);

            $porcentajeProgreso = $diasTotales > 0 ? min(100, ($diasTranscurridos / $diasTotales) * 100) : 0;

            // Predicción de rendimiento basada en progreso
            $rendimientoEstimado = min(100, $porcentajeProgreso * 1.2); // Factor de ajuste

            $prediccion = [
                'rendimiento_estimado' => round($rendimientoEstimado, 2),
                'unidad' => '%',
                'progreso_ciclo' => round($porcentajeProgreso, 2),
                'descripcion' => 'Rendimiento estimado basado en el progreso actual del ciclo'
            ];

            return response()->json([
                'success' => true,
                'tipo' => 'rendimiento',
                'datos' => $prediccion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
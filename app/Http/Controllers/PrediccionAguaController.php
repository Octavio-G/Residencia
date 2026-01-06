<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valvula;
use App\Models\RiegoManual;
use App\Models\Temperatura;
use App\Models\CicloSiembra;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PrediccionAguaController extends Controller
{
    public function index()
    {
        return view('bi.prediccion_agua');
    }

    public function predecir(Request $request)
    {
        $tipo = $request->input('tipo', 'ambos');
        
        try {
            // 1. Obtener todos los ciclos FINALIZADOS
            $ciclosCerrados = CicloSiembra::whereNotNull('fechaFin')
                ->orderBy('fechaInicio', 'asc')
                ->take(10)
                ->get();

            $labels = [];
            $dataHistorica = []; // Totales reales para la gráfica visual
            
            // Arrays auxiliares para la matemática de predicción
            $tasasDiarias = []; // Litros por día de cada ciclo
            $duraciones = [];   // Cuántos días duró cada ciclo

            // 2. Procesar cada ciclo individualmente
            foreach ($ciclosCerrados as $ciclo) {
                // Fechas
                $inicio = Carbon::parse($ciclo->fechaInicio);
                $fin = Carbon::parse($ciclo->fechaFin);
                
                // A. Calcular Duración Real del Ciclo
                // diffInDays devuelve entero. Si es 0 (mismo día), lo forzamos a 1 para evitar división por cero.
                $diasDuracion = $inicio->diffInDays($fin);
                if ($diasDuracion < 1) $diasDuracion = 1;

                // B. Calcular Consumo Total del Ciclo
                $consumoCiclo = 0;

                if ($tipo === 'valvula' || $tipo === 'ambos') {
                    $consumoCiclo += Valvula::whereBetween('fechaEncendido', [$inicio, $fin])->sum('volumen');
                }

                if ($tipo === 'manual' || $tipo === 'ambos') {
                    $consumoCiclo += RiegoManual::whereBetween('fechaEncendido', [$inicio, $fin])->sum('volumen');
                }

                // C. Guardar datos para la lógica matemática
                // Normalización: ¿Cuántos litros gastó por día este ciclo?
                $tasaDiaria = $consumoCiclo / $diasDuracion;
                
                $tasasDiarias[] = $tasaDiaria;
                $duraciones[] = $diasDuracion;

                // D. Datos visuales (Gráfica muestra totales reales)
                $labels[] = $ciclo->descripcion ?? "Ciclo " . $ciclo->id;
                $dataHistorica[] = $consumoCiclo;
            }

            // 3. Calcular Promedios Normalizados
            $conteo = count($ciclosCerrados);
            
            if ($conteo > 0) {
                $promedioLitrosDia = array_sum($tasasDiarias) / $conteo;
                $promedioDuracion = array_sum($duraciones) / $conteo;
            } else {
                $promedioLitrosDia = 0;
                $promedioDuracion = 40; // Valor por defecto si no hay historial
            }

            // 4. Factor de Ajuste Térmico
            $ultimaTemperatura = Temperatura::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
            $temperaturaActual = $ultimaTemperatura ? $ultimaTemperatura->temperatura : 25;
            
            // Si hace calor (>25°C), aumentamos la predicción
            $factorAjuste = 1 + (($temperaturaActual - 25) / 100);
            
            // 5. La Gran Fórmula de Predicción
            // (Promedio Litros/Día) * (Duración Promedio Esperada) * (Factor Calor)
            $prediccionSiguienteCiclo = ($promedioLitrosDia * $promedioDuracion) * $factorAjuste;

            // 6. Preparar respuesta para la gráfica
            $labels[] = "Próximo Ciclo (Est. " . round($promedioDuracion) . " días)";
            
            $dataGrafica = $dataHistorica;
            $dataGrafica[] = $prediccionSiguienteCiclo;

            return response()->json([
                'labels' => $labels,
                'data' => $dataGrafica,
                'prediction' => $prediccionSiguienteCiclo,
                'temperature' => $temperaturaActual,
                'promedio_historico' => ($promedioLitrosDia * $promedioDuracion), // Promedio sin ajuste térmico
                'mensaje' => "Se estima un total de " . number_format($prediccionSiguienteCiclo, 2) . " Litros para el próximo ciclo (duración est. " . round($promedioDuracion) . " días) considerando temperatura de " . $temperaturaActual . "°C."
            ]);

        } catch (\Exception $e) {
            Log::error('Error en PrediccionAguaController::predecir: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al calcular la predicción: ' . $e->getMessage()
            ], 500);
        }
    }
}
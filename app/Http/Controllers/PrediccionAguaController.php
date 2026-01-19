<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valvula;
use App\Models\RiegoManual;
use App\Models\CamaSiembra;
use App\Models\Cama2;
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

            // 4. Factor de Ajuste por Humedad del Suelo
            // Obtener promedio histórico de humedad de ambas camas
            $ultimaSemana = Carbon::now()->subDays(7);
            
            $promedioCama1 = CamaSiembra::where('fecha', '>=', $ultimaSemana)
                ->avg('humedad') ?? 70; // Valor por defecto 70%
                
            $promedioCama2 = Cama2::where('fecha', '>=', $ultimaSemana)
                ->avg('humedad') ?? 70; // Valor por defecto 70%
            
            $humedadPromedioCamas = ($promedioCama1 + $promedioCama2) / 2;
            $humedadIdeal = 70; // 70% es el nivel óptimo
            
            // Lógica de compensación inversa basada en eficiencia de humedad
            if ($humedadPromedioCamas < $humedadIdeal) {
                // Estaba muy seco, necesitamos más agua
                $factorCorreccion = 1 + (($humedadIdeal - $humedadPromedioCamas) / 100);
                // Máximo aumento del 30%
                $factorCorreccion = min($factorCorreccion, 1.3);
            } elseif ($humedadPromedioCamas > $humedadIdeal) {
                // Estaba muy húmedo, podemos ahorrar agua
                $factorCorreccion = 1 - (($humedadPromedioCamas - $humedadIdeal) / 150);
                // Mínimo de 70% del consumo original
                $factorCorreccion = max($factorCorreccion, 0.7);
            } else {
                // Humedad ideal, mantener consumo base
                $factorCorreccion = 1.0;
            }
            
            // 5. La Gran Fórmula de Predicción
            // (Promedio Litros/Día) * (Duración Promedio Esperada) * (Factor Humedad)
            $prediccionSiguienteCiclo = ($promedioLitrosDia * $promedioDuracion) * $factorCorreccion;

            // 6. Preparar respuesta para la gráfica
            $labels[] = "Próximo Ciclo (Est. " . round($promedioDuracion) . " días)";
            
            $dataGrafica = $dataHistorica;
            $dataGrafica[] = $prediccionSiguienteCiclo;

            return response()->json([
                'labels' => $labels,
                'data' => $dataGrafica,
                'prediction' => $prediccionSiguienteCiclo,
                'humedad_promedio' => round($humedadPromedioCamas, 2),
                'factor_correccion' => round($factorCorreccion, 3),
                'promedio_historico' => ($promedioLitrosDia * $promedioDuracion), // Promedio sin ajuste
                'mensaje' => "Se estima un total de " . number_format($prediccionSiguienteCiclo, 2) . " Litros para el próximo ciclo (duración est. " . round($promedioDuracion) . " días) basado en eficiencia de humedad del suelo de " . round($humedadPromedioCamas, 1) . "%."
            ]);

        } catch (\Exception $e) {
            Log::error('Error en PrediccionAguaController::predecir: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al calcular la predicción: ' . $e->getMessage()
            ], 500);
        }
    }
}
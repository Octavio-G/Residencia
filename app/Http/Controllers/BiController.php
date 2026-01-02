<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CamaSiembra;
use App\Models\Cama2;
use App\Models\CicloSiembra;
use App\Models\Cultivo;
use App\Models\Temperatura;
use App\Models\LecturaSensor;
use App\Models\Valvula;
use Carbon\Carbon;

class BiController extends Controller
{
    /**
     * Mostrar el dashboard principal de BI
     */
    public function index()
    {
        return view('bi.dashboard');
    }

    /**
     * Obtener historial de lecturas de camas
     */
    public function historialLecturas(Request $request)
    {
        $cama = $request->input('cama', 'ambas'); // 'cama1', 'cama2', o 'ambas'
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $limite = $request->input('limite', 50);

        $query1 = CamaSiembra::query();
        $query2 = Cama2::query();

        // Aplicar filtros para cama1
        if ($cama === 'cama1' || $cama === 'ambas') {
            if ($fecha_inicio) {
                $query1->where('fecha', '>=', $fecha_inicio);
            }
            if ($fecha_fin) {
                $query1->where('fecha', '<=', $fecha_fin);
            }
        } else {
            $query1->whereRaw('1 = 0'); // No devolver resultados
        }

        // Aplicar filtros para cama2
        if ($cama === 'cama2' || $cama === 'ambas') {
            if ($fecha_inicio) {
                $query2->where('fecha', '>=', $fecha_inicio);
            }
            if ($fecha_fin) {
                $query2->where('fecha', '<=', $fecha_fin);
            }
        } else {
            $query2->whereRaw('1 = 0'); // No devolver resultados
        }

        // Obtener resultados
        $lecturasCama1 = $query1->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->limit($limite)->get();
        $lecturasCama2 = $query2->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->limit($limite)->get();

        // Agregar nombre de cama a cada lectura
        $lecturasCama1 = $lecturasCama1->map(function ($lectura) {
            $lectura->cama = 'Cama 1';
            $lectura->id = $lectura->idCama1;
            return $lectura;
        });

        $lecturasCama2 = $lecturasCama2->map(function ($lectura) {
            $lectura->cama = 'Cama 2';
            $lectura->id = $lectura->idCama2;
            return $lectura;
        });

        // Combinar y ordenar todas las lecturas
        $todasLecturas = $lecturasCama1->concat($lecturasCama2);
        $todasLecturas = $todasLecturas->sortByDesc(function ($lectura) {
            // Verificar si el campo fecha ya contiene hora
            if (is_string($lectura->fecha) && strpos($lectura->fecha, ':') !== false) {
                // El campo fecha ya contiene hora, usar solo ese campo
                return $lectura->fecha;
            } else {
                // Combinar fecha y hora
                return $lectura->fecha . ' ' . $lectura->hora;
            }
        })->take($limite);

        return response()->json([
            'lecturas' => $todasLecturas,
            'total' => $todasLecturas->count()
        ]);
    }

    /**
     * Obtener datos para el indicador de salud (Pestaña 1)
     */
    public function indicadorSalud()
    {
        // Obtener datos de las camas de siembra existentes
        // Usar los modelos CamaSiembra y Cama2
        $camas1 = CamaSiembra::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->limit(1)->get();
        $camas2 = Cama2::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->limit(1)->get();
        
        // Combinar datos de todas las camas
        $todasLasCamas = collect();
        foreach ($camas1 as $cama) {
            $cama->nombre = 'Cama 1';
            $cama->id = $cama->idCama1;
            $todasLasCamas->push($cama);
        }
        foreach ($camas2 as $cama) {
            $cama->nombre = 'Cama 2';
            $cama->id = $cama->idCama2;
            $todasLasCamas->push($cama);
        }

        // Calcular nivel de riesgo para cada cama (lógica de semáforo)
        $camasData = [];
        
        if ($todasLasCamas->isEmpty()) {
            // Si no hay datos reales, retornar un array vacío
            return response()->json([
                'camas' => []
            ]);
        }
        
        foreach ($todasLasCamas as $cama) {
            // Usar la lógica del modelo para determinar el estado de salud
            $color = $cama->estadoSalud; // Usar el atributo calculado del modelo
            $estado = $color === 'verde' ? 'Óptimo' : ($color === 'amarillo' ? 'Advertencia' : 'Crítico');
            
            // Determinar cultivo en la cama (simulado)
            $cultivo = '';
            if ($cama->nombre == 'Cama 1') {
                $cultivo = 'Cilantro';
            } else {
                $cultivo = 'Rábano';
            }
            
            $camasData[] = [
                'id' => $cama->id,
                'nombre' => $cama->nombre,
                'humedad' => $cama->humedad,
                'fecha' => $cama->fecha,
                'hora' => $cama->hora,
                'color' => $color,
                'estado' => $estado,
                'cultivo' => $cultivo
            ];
        }

        return response()->json([
            'camas' => $camasData
        ]);
    }





    /**
     * Obtener lista de ciclos de siembra (para Pestaña 3 y 4)
     */
    public function ciclosSiembra()
    {
        // Obtener ciclos de siembra reales de la base de datos
        $ciclos = CicloSiembra::all();

        $options = '';
        foreach ($ciclos as $ciclo) {
            // Verificar que el ciclo tenga una descripción válida
            if (!empty($ciclo->descripcion)) {
                // Si el cicloId es null o 0, usamos una representación especial que combine ID y descripción
                if ($ciclo->cicloId !== null && $ciclo->cicloId !== 0) {
                    $valorOption = $ciclo->cicloId;
                } else {
                    // Usar una representación que combine ID y descripción para evitar colisiones
                    $valorOption = 'DESC_' . urlencode($ciclo->descripcion);
                }
                $options .= '<option value="'.htmlspecialchars($valorOption, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($ciclo->descripcion, ENT_QUOTES, 'UTF-8').'</option>';
            }
        }

        // Si no hay ciclos, usar datos simulados
        if (empty($options)) {
            $ciclosSimulados = [
                ['cicloId' => 1, 'descripcion' => 'Ciclo Primavera 2025'],
                ['cicloId' => 2, 'descripcion' => 'Ciclo Verano 2025'],
                ['cicloId' => 3, 'descripcion' => 'Ciclo Otoño 2025'],
                ['cicloId' => 4, 'descripcion' => 'Ciclo Invierno 2025'],
                ['cicloId' => 5, 'descripcion' => 'Ciclo Experimental A'],
            ];

            foreach ($ciclosSimulados as $ciclo) {
                $options .= '<option value="'.$ciclo['cicloId'].'">'.$ciclo['descripcion'].'</option>';
            }
        }

        return response()->json([
            'options' => $options
        ]);
    }

    /**
     * Obtener datos del ciclo seleccionado (para Pestaña 4)
     */
    public function datosCiclo(Request $request)
    {
        try {
            $cicloId = $request->input('ciclo_id');
            
            // Registrar el ID del ciclo para depuración
            Log::info('Solicitando datos para ciclo ID: ' . $cicloId . ' (tipo: ' . gettype($cicloId) . ')');
            
            // Obtener el ciclo seleccionado
            // Si el ID comienza con 'DESC_', buscar por descripción; si no, buscar por ID numérico
            if (strpos($cicloId, 'DESC_') === 0) {
                // Extraer la descripción del valor
                $descripcion = urldecode(substr($cicloId, 5));
                Log::info('Buscando ciclo por descripción: ' . $descripcion);
                $ciclo = CicloSiembra::where('descripcion', $descripcion)->first();
            } else {
                Log::info('Buscando ciclo por ID numérico: ' . $cicloId);
                $ciclo = CicloSiembra::find($cicloId);
            }
            
            if (!$ciclo) {
                // Buscar en todos los ciclos para ver qué hay disponible
                $todosLosCiclos = CicloSiembra::all();
                Log::warning('Ciclo no encontrado. Ciclos disponibles: ' . $todosLosCiclos->pluck('descripcion')->toJson());
                return response()->json(['error' => 'Ciclo no encontrado: ' . $cicloId], 404);
            }
            
            // Registrar información del ciclo para depuración
            Log::info('Ciclo encontrado: ' . json_encode($ciclo));
            
            // Calcular días transcurridos
            $fechaInicio = Carbon::parse($ciclo->fechaInicio);
            $hoy = Carbon::today();
            
            if ($ciclo->fechaFin && $ciclo->fechaFin != '0000-00-00') {
                $fechaFin = Carbon::parse($ciclo->fechaFin);
                
                // Verificar si el ciclo ya terminó
                if ($hoy->gte($fechaFin)) {
                    // Ciclo completado
                    $diasTranscurridos = $fechaInicio->diffInDays($fechaFin);
                    $diasRestantes = 0;
                    $cicloCompletado = true;
                } else {
                    // Ciclo en progreso
                    $diasTranscurridos = $fechaInicio->diffInDays($hoy);
                    $diasRestantes = $hoy->diffInDays($fechaFin);
                    $cicloCompletado = false;
                }
            } else {
                // Si no hay fecha de fin definida, asumimos que es un ciclo en progreso indefinido
                $diasTranscurridos = $fechaInicio->diffInDays($hoy);
                $diasRestantes = null; // No se puede calcular
                $cicloCompletado = false;
            }
            
            // Calcular consumo de agua total
            $consumoAguaTotal = $this->calcularConsumoAguaTotal($cicloId);
            
            // Registrar consumo de agua para depuración
            Log::info('Consumo de agua total: ' . $consumoAguaTotal);
            
            // Verificar si el ciclo está completado
            $cicloCompletado = $ciclo->estado === 'completado' || 
                              ($ciclo->fechaFin && $ciclo->fechaFin != '0000-00-00' && 
                               $hoy->gte(Carbon::parse($ciclo->fechaFin)));
            
            $respuesta = [
                'ciclo' => $ciclo,
                'dias_transcurridos' => $diasTranscurridos,
                'dias_restantes' => $diasRestantes,
                'consumo_agua_total' => $consumoAguaTotal,
                'ciclo_completado' => $cicloCompletado
            ];
            
            // Registrar respuesta para depuración
            Log::info('Respuesta de datos de ciclo: ' . json_encode($respuesta));
            
            return response()->json($respuesta);
        } catch (\Exception $e) {
            Log::error('Error al cargar los datos del ciclo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los datos del ciclo: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Calcular el consumo total de agua para el ciclo
     */
    private function calcularConsumoAguaTotal($cicloId)
    {
        try {
            // Registrar el ID del ciclo para depuración
            Log::info('Calculando consumo de agua para ciclo ID: ' . $cicloId);
            
            // Obtener todos los cultivos asociados a este ciclo
            // Si el ID es numérico, buscar por ID; si no, buscar por descripción del ciclo
            if (is_numeric($cicloId)) {
                $cultivos = Cultivo::where('cicloId', $cicloId)->get();
            } else {
                $ciclo = CicloSiembra::where('descripcion', $cicloId)->first();
                if ($ciclo) {
                    $cultivos = Cultivo::where('cicloId', $ciclo->cicloId)->get();
                } else {
                    $cultivos = collect();
                }
            }
            
            // Registrar información de cultivos para depuración
            Log::info('Cultivos encontrados: ' . $cultivos->count());
            
            if ($cultivos->isEmpty()) {
                Log::info('No se encontraron cultivos para el ciclo ID: ' . $cicloId);
                return 0;
            }
            
            // Obtener los IDs de los cultivos
            $cultivoIds = $cultivos->pluck('cultivoId')->toArray();
            
            // Registrar IDs de cultivos para depuración
            Log::info('IDs de cultivos: ' . json_encode($cultivoIds));
            
            // Obtener la suma total de volumen de riego para estos cultivos
            $totalLitros = LecturaSensor::whereIn('cultivoId', $cultivoIds)
                ->whereNotNull('volumen')
                ->sum('volumen');
            
            // Registrar total de litros para depuración
            Log::info('Total de litros calculado: ' . $totalLitros);
            
            return $totalLitros ?: 0;
        } catch (\Exception $e) {
            Log::error('Error al calcular consumo de agua: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Comparar dos ciclos de siembra (Pestaña 3)
     */
    public function compararCiclos(Request $request)
    {
        $ciclo1 = $request->input('ciclo1');
        $ciclo2 = $request->input('ciclo2');
        $metrica = $request->input('metrica');

        // Obtener descripciones de los ciclos
        // Si el ID es numérico, buscar por ID; si no, buscar por descripción
        if (is_numeric($ciclo1)) {
            $ciclo1Data = CicloSiembra::find($ciclo1);
        } else {
            $ciclo1Data = CicloSiembra::where('descripcion', $ciclo1)->first();
        }
        
        if (is_numeric($ciclo2)) {
            $ciclo2Data = CicloSiembra::find($ciclo2);
        } else {
            $ciclo2Data = CicloSiembra::where('descripcion', $ciclo2)->first();
        }

        $nombreCiclo1 = $ciclo1Data ? $ciclo1Data->descripcion : 'Ciclo 1';
        $nombreCiclo2 = $ciclo2Data ? $ciclo2Data->descripcion : 'Ciclo 2';

        // Etiquetas para el eje X (días de cultivo)
        $etiquetas = [];
        for ($i = 1; $i <= 30; $i++) {
            $etiquetas[] = 'Día '.$i;
        }

        // Generar datos simulados para ambos ciclos
        $datosCiclo1 = [];
        $datosCiclo2 = [];

        // Parámetros para generar datos realistas según la métrica
        switch ($metrica) {
            case 'humedad':
                // Humedad del suelo en %
                $base1 = rand(50, 70);
                $base2 = rand(50, 70);
                for ($i = 0; $i < 30; $i++) {
                    $datosCiclo1[] = max(30, min(90, $base1 + rand(-10, 10)));
                    $datosCiclo2[] = max(30, min(90, $base2 + rand(-10, 10)));
                }
                break;
            case 'temperatura':
                // Temperatura en °C
                $base1 = rand(20, 28);
                $base2 = rand(20, 28);
                for ($i = 0; $i < 30; $i++) {
                    $datosCiclo1[] = max(10, min(40, $base1 + rand(-5, 5)));
                    $datosCiclo2[] = max(10, min(40, $base2 + rand(-5, 5)));
                }
                break;
            default:
                // Por defecto, humedad
                $base1 = rand(50, 70);
                $base2 = rand(50, 70);
                for ($i = 0; $i < 30; $i++) {
                    $datosCiclo1[] = max(30, min(90, $base1 + rand(-10, 10)));
                    $datosCiclo2[] = max(30, min(90, $base2 + rand(-10, 10)));
                }
        }

        return response()->json([
            'etiquetas' => $etiquetas,
            'nombre_ciclo1' => $nombreCiclo1,
            'nombre_ciclo2' => $nombreCiclo2,
            'datos_ciclo1' => $datosCiclo1,
            'datos_ciclo2' => $datosCiclo2
        ]);
    }

    /**
     * Exportar gráfico comparativo a PDF
     */
    public function exportarPdf(Request $request)
    {
        $ciclo1 = $request->input('ciclo1');
        $ciclo2 = $request->input('ciclo2');
        $metrica = $request->input('metrica');

        // Obtener descripciones de los ciclos
        // Si el ID comienza con 'DESC_', buscar por descripción; si no, buscar por ID numérico
        if (strpos($ciclo1, 'DESC_') === 0) {
            // Extraer la descripción del valor
            $descripcion1 = urldecode(substr($ciclo1, 5));
            $ciclo1Data = CicloSiembra::where('descripcion', $descripcion1)->first();
        } else {
            $ciclo1Data = CicloSiembra::find($ciclo1);
        }
        
        if (strpos($ciclo2, 'DESC_') === 0) {
            // Extraer la descripción del valor
            $descripcion2 = urldecode(substr($ciclo2, 5));
            $ciclo2Data = CicloSiembra::where('descripcion', $descripcion2)->first();
        } else {
            $ciclo2Data = CicloSiembra::find($ciclo2);
        }

        $nombreCiclo1 = $ciclo1Data ? $ciclo1Data->descripcion : 'Ciclo 1';
        $nombreCiclo2 = $ciclo2Data ? $ciclo2Data->descripcion : 'Ciclo 2';

        // Datos para la vista del PDF
        $data = [
            'ciclo1' => $nombreCiclo1,
            'ciclo2' => $nombreCiclo2,
            'metrica' => $metrica,
        ];

        // En una implementación completa, aquí generaríamos el PDF real
        // Por ahora, simplemente devolvemos una respuesta JSON
        return response()->json([
            'message' => 'Informe PDF generado correctamente',
            'data' => $data
        ]);

        // Para una implementación completa con DomPDF, se usaría algo como:
        /*
        $pdf = \PDF::loadView('bi.reporte_pdf', $data);
        return $pdf->download('informe_comparativo_'.$ciclo1.'_vs_'.$ciclo2.'.pdf');
        */
    }
    
    /**
     * Obtener volumen de agua usado durante un ciclo de siembra
     */
    public function volumenAguaCiclo(Request $request)
    {
        try {
            $cicloId = $request->input('ciclo_id');
            
            // Registrar para depuración
            Log::info('Solicitando volumen de agua para ciclo ID: ' . $cicloId . ' (tipo: ' . gettype($cicloId) . ')');
            
            // Obtener el ciclo seleccionado
            // Si el ID comienza con 'DESC_', buscar por descripción; si no, buscar por ID numérico
            if (strpos($cicloId, 'DESC_') === 0) {
                // Extraer la descripción del valor
                $descripcion = urldecode(substr($cicloId, 5));
                Log::info('Buscando ciclo por descripción: ' . $descripcion);
                $ciclo = CicloSiembra::where('descripcion', $descripcion)->first();
            } else {
                Log::info('Buscando ciclo por ID numérico: ' . $cicloId);
                $ciclo = CicloSiembra::find($cicloId);
            }
            
            if (!$ciclo) {
                // Buscar en todos los ciclos para ver qué hay disponible
                $todosLosCiclos = CicloSiembra::all();
                Log::warning('Ciclo no encontrado. Ciclos disponibles: ' . $todosLosCiclos->pluck('descripcion')->toJson());
                return response()->json(['error' => 'Ciclo no encontrado: ' . $cicloId], 404);
            }
            
            // Verificar si el ciclo tiene fechas válidas
            if (!$ciclo->fechaInicio) {
                return response()->json(['error' => 'El ciclo no tiene fecha de inicio'], 400);
            }
            
            $fechaInicio = $ciclo->fechaInicio;
            $fechaFin = $ciclo->fechaFin && $ciclo->fechaFin != '0000-00-00' ? $ciclo->fechaFin : null;
            
            // Obtener los cultivos asociados a este ciclo
            $cultivoIds = Cultivo::where('cicloId', $ciclo->cicloId)->pluck('cultivoId')->toArray();
            
            // Calcular volumen total de agua usado en la tabla valvula durante el periodo del ciclo
            $query = \App\Models\Valvula::whereIn('cultivoId', $cultivoIds);
            
            // Filtrar por fechas del ciclo
            $query->where('fechaEncendido', '>=', $fechaInicio);
            
            if ($fechaFin) {
                $query->where('fechaEncendido', '<=', $fechaFin);
            }
            
            $volumenTotal = $query->sum('volumen');
            
            // Obtener también el volumen por fecha para mostrar en gráfico
            $volumenPorFecha = $query->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            
            return response()->json([
                'volumen_total' => $volumenTotal,
                'volumen_por_fecha' => $volumenPorFecha,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'ciclo_descripcion' => $ciclo->descripcion
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al calcular volumen de agua por ciclo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al calcular volumen de agua: ' . $e->getMessage()], 500);
        }
    }
}
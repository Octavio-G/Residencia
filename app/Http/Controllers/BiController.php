<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CamaSiembra;
use App\Models\Cama2;
use App\Models\CicloSiembra;
use App\Models\Cultivo;
use App\Models\Valvula;
use App\Models\RiegoManual;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * Obtener volumen de agua usado durante un ciclo de siembra
     */
    public function volumenAguaCiclo(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        
        // Obtener el ciclo seleccionado
        $ciclo = CicloSiembra::where('cicloId', $cicloId)->first();
        if (!$ciclo) {
            return response()->json(['error' => 'Ciclo no encontrado'], 404);
        }
        
        // Obtener IDs de cultivos asociados al ciclo
        $cultivoIds = Cultivo::where('cicloId', $cicloId)->pluck('cultivoId');
        
        // Calcular volumen total de agua usado en la tabla valvula durante el periodo del ciclo
        $volumenTotal = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
            ->sum('volumen');
        
        // Si no hay datos en el periodo exacto, buscar en rango ampliado
        if ($volumenTotal == 0) {
            $fechaInicioAmpliada = date('Y-m-d H:i:s', strtotime($ciclo->fechaInicio . ' -30 days'));
            $fechaFinAmpliada = date('Y-m-d H:i:s', strtotime($ciclo->fechaFin . ' +30 days'));
            
            $volumenTotal = Valvula::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                ->sum('volumen');
            
            // Usar el rango ampliado para el gráfico también
            $volumenPorFecha = Valvula::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                ->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
        } else {
            // Obtener también el volumen por fecha para mostrar en gráfico
            $volumenPorFecha = Valvula::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
                ->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
        }
        
        return response()->json([
            'volumen_total' => $volumenTotal,
            'volumen_por_fecha' => $volumenPorFecha,
            'fecha_inicio' => $ciclo->fechaInicio,
            'fecha_fin' => $ciclo->fechaFin,
            'ciclo_descripcion' => $ciclo->descripcion
        ]);
    }

    /**
     * Función de prueba para verificar volumen de agua - PANEL DE PRUEBA
     */
    public function testVolumenPanel(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        
        // Obtener el ciclo
        $ciclo = CicloSiembra::where('cicloId', $cicloId)->first();
        if (!$ciclo) {
            return response()->json(['error' => 'Ciclo no encontrado'], 404);
        }
        
        // Obtener cultivos
        $cultivoIds = Cultivo::where('cicloId', $cicloId)->pluck('cultivoId');
        
        // Calcular volúmenes con información detallada
        $volumenValvula = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
            ->sum('volumen');
            
        $volumenRiegoManual = RiegoManual::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
            ->sum('volumen');
            
        $consumoTotal = $volumenValvula + $volumenRiegoManual;
        
        // Información de depuración
        $registrosValvula = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
            ->count();
            
        $registrosRiego = RiegoManual::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
            ->count();
        
        return response()->json([
            'ciclo_id' => $cicloId,
            'ciclo_descripcion' => $ciclo->descripcion,
            'periodo' => [
                'inicio' => $ciclo->fechaInicio,
                'fin' => $ciclo->fechaFin
            ],
            'resultados' => [
                'volumen_valvula' => round($volumenValvula, 2),
                'volumen_riego_manual' => round($volumenRiegoManual, 2),
                'consumo_total' => round($consumoTotal, 2)
            ],
            'detalles' => [
                'registros_valvula' => $registrosValvula,
                'registros_riego_manual' => $registrosRiego,
                'cultivos_encontrados' => $cultivoIds->count()
            ],
            'mensaje' => 'Datos obtenidos correctamente'
        ]);
    }

    /**
     * Obtener volumen de agua usado en válvulas durante un ciclo de siembra
     */
    public function volumenAguaValvulaCiclo(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        $ciclo = CicloSiembra::where('cicloId', $cicloId)->first();
        
        if (!$ciclo) { return response()->json(['error' => 'Ciclo no encontrado'], 404); }
        
        $cultivoIds = Cultivo::where('cicloId', $cicloId)->pluck('cultivoId');

        // --- AQUÍ ESTÁ EL ARREGLO ---
        // Forzar inicio a las 00:00:00 y fin a las 23:59:59
        $fechaInicio = Carbon::parse($ciclo->fechaInicio)->startOfDay();
        $fechaFin = Carbon::parse($ciclo->fechaFin)->endOfDay();

        // 1. INTENTO ESTRICTO (Por Cultivo y Fechas)
        $volumenTotal = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicio, $fechaFin])
            ->sum('volumen');
        
        // 2. INTENTO POR FECHAS (Fallback si el estricto falló)
        // Asumimos que si hubo riego en esas fechas, pertenece al ciclo activo
        if ($volumenTotal == 0) {
            Log::info("Intento estricto dio 0. Buscando solo por fechas: $fechaInicio a $fechaFin");
            
            $volumenTotal = Valvula::whereBetween('fechaEncendido', [$fechaInicio, $fechaFin])
                ->sum('volumen');
        }
        
        // Preparar datos para gráfica (usando la misma lógica de fallback)
        $queryGrafica = Valvula::whereBetween('fechaEncendido', [$fechaInicio, $fechaFin]);
        if ($volumenTotal > 0 && Valvula::whereIn('cultivoId', $cultivoIds)->exists()) {
             // Si el filtro de cultivo funcionó, úsalo también para la gráfica
             $queryGrafica->whereIn('cultivoId', $cultivoIds);
        }
        
        $volumenPorFecha = $queryGrafica
            ->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        return response()->json([
            'volumen_total' => $volumenTotal,
            'volumen_por_fecha' => $volumenPorFecha,
            'fecha_inicio' => $ciclo->fechaInicio,
            'fecha_fin' => $ciclo->fechaFin,
            'ciclo_descripcion' => $ciclo->descripcion
        ]);
    }

    /**
     * Obtener volumen de agua usado en riego manual durante un ciclo de siembra
     */
    public function volumenAguaRiegoManualCiclo(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        
        // Obtener el ciclo seleccionado
        $ciclo = CicloSiembra::where('cicloId', $cicloId)->first();
        if (!$ciclo) {
            return response()->json(['error' => 'Ciclo no encontrado'], 404);
        }
        
        // Obtener IDs de cultivos asociados al ciclo
        $cultivoIds = Cultivo::where('cicloId', $cicloId)->pluck('cultivoId');
        
        // --- AQUÍ ESTÁ EL ARREGLO ---
        // Forzar inicio a las 00:00:00 y fin a las 23:59:59
        $fechaInicio = Carbon::parse($ciclo->fechaInicio)->startOfDay();
        $fechaFin = Carbon::parse($ciclo->fechaFin)->endOfDay();

        // Calcular volumen total de agua usado en la tabla riegomanual durante el periodo del ciclo
        $volumenTotal = RiegoManual::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicio, $fechaFin])
            ->sum('volumen');
        
        // Si no hay datos en el periodo exacto, buscar en rango ampliado
        if ($volumenTotal == 0) {
            $fechaInicioAmpliada = $fechaInicio->copy()->subDays(30);
            $fechaFinAmpliada = $fechaFin->copy()->addDays(30);
            
            $volumenTotal = RiegoManual::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                ->sum('volumen');
            
            // Usar el rango ampliado para el gráfico también
            $volumenPorFecha = RiegoManual::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                ->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
        } else {
            // Obtener también el volumen por fecha para mostrar en gráfico
            $volumenPorFecha = RiegoManual::whereIn('cultivoId', $cultivoIds)
                ->whereBetween('fechaEncendido', [$ciclo->fechaInicio, $ciclo->fechaFin])
                ->selectRaw('DATE(fechaEncendido) as fecha, SUM(volumen) as volumen')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
        }
        
        return response()->json([
            'volumen_total' => $volumenTotal,
            'volumen_por_fecha' => $volumenPorFecha,
            'fecha_inicio' => $ciclo->fechaInicio,
            'fecha_fin' => $ciclo->fechaFin,
            'ciclo_descripcion' => $ciclo->descripcion
        ]);
    }
    
    /**
     * Obtener lista de ciclos de siembra (para Pestaña 3 y 4)
     */
    public function ciclosSiembra(Request $request)
    {
        $ciclos = CicloSiembra::orderBy('fechaInicio', 'desc')
            ->get(['cicloId', 'descripcion', 'fechaInicio', 'fechaFin']);
        
        Log::info('Ciclos encontrados en la base de datos: ' . $ciclos->count());
        
        // Crear mapeo de índices a IDs (UUIDs o numéricas)
        $indiceAMapeo = [];
        $options = '';
        
        foreach ($ciclos as $index => $ciclo) {
            $indiceId = 'IDX_' . $index;
            $indiceAMapeo[$indiceId] = $ciclo->cicloId;
            
            // Generar opción con índice en lugar del ID completo (aceptar UUIDs o IDs numéricas)
            $options .= '<option value="' . $indiceId . '">' . $ciclo->descripcion . ' (' . $ciclo->fechaInicio . ' - ' . ($ciclo->fechaFin ?? 'En curso') . ')</option>';
            
            Log::info('Opción generada: ' . $indiceId . ' -> ' . $ciclo->cicloId . ' (' . $ciclo->descripcion . ')');
        }
        
        // Almacenar el mapeo en la sesión
        session(['indice_ciclo_mapping' => $indiceAMapeo]);
        
        Log::info('Mapeo almacenado en sesión. Total opciones: ' . count($indiceAMapeo));
        Log::info('Total de opciones generadas: ' . substr_count($options, '<option'));

        return response()->json([
            'success' => true,
            'options' => $options
        ]);
    }
    
    /**
     * Obtener datos del ciclo seleccionado (para Pestaña 4)
     */
    public function datosCiclo(Request $request)
    {
        $indiceId = $request->input('ciclo_id');
            
        // Depuración: ver qué índice se está recibiendo
        Log::info('Solicitando ciclo con índice: ' . $indiceId . ' (tipo: ' . gettype($indiceId) . ')');
            
        // Obtener el mapeo de la sesión
        $indiceAMapeo = session('indice_ciclo_mapping', []);
            
        // Verificar que el índice exista en el mapeo
        if (!isset($indiceAMapeo[$indiceId])) {
            Log::warning('Índice no encontrado en el mapeo: ' . $indiceId);
            Log::warning('Mapeo disponible: ' . json_encode(array_keys($indiceAMapeo)));
            return response()->json(['error' => 'Índice de ciclo no válido: ' . $indiceId], 400);
        }
            
        // Obtener el UUID real del mapeo
        $cicloIdReal = $indiceAMapeo[$indiceId];
        Log::info('Mapeando índice ' . $indiceId . ' a UUID real: ' . $cicloIdReal);
            
        // Buscar el ciclo usando el UUID real
        $ciclo = CicloSiembra::where('cicloId', $cicloIdReal)->first();
            
        if (!$ciclo) {
            // Verificar qué ciclos existen en la base de datos
            $todosLosCiclos = CicloSiembra::all(['cicloId', 'descripcion'])->toArray();
            Log::warning('Ciclo no encontrado. Ciclos disponibles: ' . json_encode($todosLosCiclos));
            return response()->json(['error' => 'Ciclo no encontrado para índice: ' . $indiceId], 404);
        }
        
        // Calcular días transcurridos
        try {
            $fechaInicio = Carbon::parse($ciclo->fechaInicio);
        } catch (Exception $e) {
            // Si hay un problema con el formato de fecha, usar una fecha por defecto
            $fechaInicio = Carbon::now();
            Log::warning('Formato de fecha inválido para fechaInicio: ' . $ciclo->fechaInicio);
        }
        
        $fechaFin = null;
        if ($ciclo->fechaFin) {
            try {
                $fechaFin = Carbon::parse($ciclo->fechaFin);
            } catch (Exception $e) {
                Log::warning('Formato de fecha inválido para fechaFin: ' . $ciclo->fechaFin);
            }
        }
        
        $fechaActual = Carbon::now();
        
        // Calcular la diferencia en días
        // Si el ciclo ya ha terminado, los días transcurridos son entre inicio y fin
        // Si el ciclo está en curso, los días transcurridos son entre inicio y fecha actual
        if ($ciclo->fechaFin && $fechaFin && $fechaActual->gte($fechaFin)) {
            // Ciclo completado: calcular días entre inicio y fin
            $diasTranscurridos = max(0, $fechaInicio->diffInDays($fechaFin));
        } else {
            // Ciclo en curso: calcular días entre inicio y fecha actual
            $diasTranscurridos = max(0, $fechaInicio->diffInDays($fechaActual));
        }
        
        // Determinar si el ciclo está completado (basado en fechaFin)
        $cicloCompletado = $fechaFin && $fechaActual->gte($fechaFin);
        
        // Calcular días restantes si no está completado
        $diasRestantes = null;
        if (!$cicloCompletado && $fechaFin) {
            $diasRestantes = max(0, $fechaActual->diffInDays($fechaFin));
        }
        
        // Obtener IDs de cultivos asociados al ciclo
        $cultivoIds = Cultivo::where('cicloId', $cicloIdReal)->pluck('cultivoId');
        
        // Calcular consumo de agua total DURANTE EL PERIODO DEL CICLO
        // Primero intentar con el periodo exacto del ciclo
        
        // --- AQUÍ ESTÁ EL ARREGLO ---
        // Forzar inicio a las 00:00:00 y fin a las 23:59:59
        $fechaInicioPeriodo = Carbon::parse($ciclo->fechaInicio)->startOfDay();
        $fechaFinPeriodo = Carbon::parse($ciclo->fechaFin)->endOfDay();
        
        // Intentar primero con la relación a través de cultivoId
        $volumenValvula = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicioPeriodo, $fechaFinPeriodo])
            ->sum('volumen');
        
        $volumenRiegoManual = RiegoManual::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicioPeriodo, $fechaFinPeriodo])
            ->sum('volumen');
        
        // Si no hay datos con la relación a cultivoId, buscar solo por fechas
        if ($volumenValvula == 0 && $volumenRiegoManual == 0) {
            $volumenValvula = Valvula::whereBetween('fechaEncendido', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->sum('volumen');
            
            $volumenRiegoManual = RiegoManual::whereBetween('fechaEncendido', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->sum('volumen');
            
            // Si aún no hay datos, buscar en un rango ampliado (±30 días)
            if ($volumenValvula == 0 && $volumenRiegoManual == 0) {
                $fechaInicioAmpliada = Carbon::parse($ciclo->fechaInicio)->subDays(30)->startOfDay();
                $fechaFinAmpliada = Carbon::parse($ciclo->fechaFin)->addDays(30)->endOfDay();
                
                $volumenValvula = Valvula::whereIn('cultivoId', $cultivoIds)
                    ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                    ->sum('volumen');
                
                $volumenRiegoManual = RiegoManual::whereIn('cultivoId', $cultivoIds)
                    ->whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                    ->sum('volumen');
                
                // Si aún no hay datos con cultivoId, buscar solo por fechas ampliadas
                if ($volumenValvula == 0 && $volumenRiegoManual == 0) {
                    $volumenValvula = Valvula::whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                        ->sum('volumen');
                    
                    $volumenRiegoManual = RiegoManual::whereBetween('fechaEncendido', [$fechaInicioAmpliada, $fechaFinAmpliada])
                        ->sum('volumen');
                }
            }
        }
        
        $consumoAguaTotal = $volumenValvula + $volumenRiegoManual;
        
        return response()->json([
            'ciclo' => [
                'id' => $ciclo->cicloId,
                'descripcion' => $ciclo->descripcion,
                'fechaInicio' => $ciclo->fechaInicio,
                'fechaFin' => $ciclo->fechaFin,
                'estado' => $cicloCompletado ? 'completado' : 'activo',
            ],
            'dias_transcurridos' => $diasTranscurridos,
            'dias_restantes' => $diasRestantes,
            'consumo_agua_total' => $consumoAguaTotal,
            'consumo_valvula' => $volumenValvula,
            'consumo_manual' => $volumenRiegoManual,
            'ciclo_completado' => $cicloCompletado
        ]);
    }
}
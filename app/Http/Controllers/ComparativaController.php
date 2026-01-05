<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CicloSiembra;
use App\Models\CamaSiembra;
use App\Models\Cama2;
use App\Models\RiegoManual;
use App\Models\Valvula;
use Carbon\Carbon;

class ComparativaController extends Controller
{
    /**
     * Mostrar la vista de comparativa histórica
     */
    public function index()
    {
        return view('bi.comparativa_historica');
    }
    
    /**
     * Obtener lista de ciclos finalizados para los selectores
     */
    public function getCiclosFinalizados()
    {
        try {
            // Obtener solo ciclos finalizados (fecha_fin no nula y fecha_inicio existe)
            $ciclos = CicloSiembra::whereNotNull('fechaFin')
                ->where('fechaFin', '!=', '0000-00-00')
                ->whereNotNull('fechaInicio')
                ->where('fechaInicio', '!=', '0000-00-00')
                ->orderBy('fechaInicio', 'desc')
                ->get();
            
            // Crear mapeo de índices a UUIDs en la sesión (como en BiController)
            $mapeoCiclos = [];
            $options = '';
            
            foreach ($ciclos as $index => $ciclo) {
                $mapeoCiclos[$index] = $ciclo->cicloId;
                $descripcion = $ciclo->descripcion ?? 'Ciclo ' . $ciclo->cicloId;
                $fechas = '(' . $ciclo->fechaInicio . ' al ' . $ciclo->fechaFin . ')';
                $options .= '<option value="IDX_' . $index . '">' . htmlspecialchars($descripcion . ' ' . $fechas, ENT_QUOTES) . '</option>';
            }
            
            // Guardar mapeo en sesión
            session(['mapeo_ciclos_comparativa' => $mapeoCiclos]);
            
            if (empty($options)) {
                $options = '<option value="">No hay ciclos finalizados disponibles</option>';
            }
            
            return response()->json([
                'success' => true,
                'options' => $options,
                'ciclos' => $ciclos->toArray()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener ciclos finalizados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar ciclos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Comparar dos ciclos de siembra
     */
    public function compararCiclos(Request $request)
    {
        try {
            // Registrar los datos recibidos para debugging
            Log::info('Datos recibidos en compararCiclos:', [
                'ciclo_a' => $request->input('ciclo_a'),
                'ciclo_b' => $request->input('ciclo_b'),
                'tipo_dato' => $request->input('tipo_dato'),
                'tipo_riego' => $request->input('tipo_riego'),
                'todos_los_datos' => $request->all()
            ]);
            
            $cicloAId = $request->input('ciclo_a');
            $cicloBId = $request->input('ciclo_b');
            $tipoDato = $request->input('tipo_dato', 'humedad_cama1');
            $tipoRiego = $request->input('tipo_riego', 'total');
            
            // Validar que se proporcionen ambos ciclos (con validación más estricta)
            if (!$cicloAId || $cicloAId === '' || !$cicloBId || $cicloBId === '') {
                Log::warning('Validación fallida - IDs recibidos:', [
                    'ciclo_a' => $cicloAId,
                    'ciclo_b' => $cicloBId,
                    'ciclo_a_tipo' => gettype($cicloAId),
                    'ciclo_b_tipo' => gettype($cicloBId)
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Debe seleccionar ambos ciclos para comparar'
                ], 400);
            }
            
            // Obtener información de ambos ciclos
            Log::info('Buscando ciclos con IDs:', [
                'ciclo_a_id' => $cicloAId,
                'ciclo_b_id' => $cicloBId
            ]);
            
            // Manejar el sistema de índices (como en BiController)
            $cicloA = null;
            $cicloB = null;
            
            if (strpos($cicloAId, 'IDX_') === 0) {
                // Extraer el índice numérico
                $indiceA = intval(substr($cicloAId, 4));
                Log::info('Buscando ciclo A por índice: ' . $indiceA);
                
                // Obtener el mapeo de la sesión
                $mapeoCiclos = session('mapeo_ciclos_comparativa', []);
                
                if (isset($mapeoCiclos[$indiceA])) {
                    $uuidA = $mapeoCiclos[$indiceA];
                    Log::info('UUID encontrado para ciclo A: ' . $uuidA);
                    $cicloA = CicloSiembra::find($uuidA);
                }
            } else {
                $cicloA = CicloSiembra::find($cicloAId);
            }
            
            if (strpos($cicloBId, 'IDX_') === 0) {
                // Extraer el índice numérico
                $indiceB = intval(substr($cicloBId, 4));
                Log::info('Buscando ciclo B por índice: ' . $indiceB);
                
                // Obtener el mapeo de la sesión
                $mapeoCiclos = session('mapeo_ciclos_comparativa', []);
                
                if (isset($mapeoCiclos[$indiceB])) {
                    $uuidB = $mapeoCiclos[$indiceB];
                    Log::info('UUID encontrado para ciclo B: ' . $uuidB);
                    $cicloB = CicloSiembra::find($uuidB);
                }
            } else {
                $cicloB = CicloSiembra::find($cicloBId);
            }
            
            Log::info('Resultados de búsqueda:', [
                'ciclo_a_encontrado' => $cicloA ? 'si' : 'no',
                'ciclo_b_encontrado' => $cicloB ? 'si' : 'no',
                'ciclo_a_id_real' => $cicloA ? $cicloA->cicloId : 'null',
                'ciclo_b_id_real' => $cicloB ? $cicloB->cicloId : 'null'
            ]);
            
            if (!$cicloA || !$cicloB) {
                // Buscar todos los ciclos disponibles para debugging
                $todosLosCiclos = CicloSiembra::all();
                $idsDisponibles = $todosLosCiclos->pluck('cicloId')->toArray();
                
                Log::warning('Ciclos disponibles en BD:', $idsDisponibles);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Uno o ambos ciclos no existen. IDs buscados: A=' . $cicloAId . ', B=' . $cicloBId . '. IDs disponibles: ' . implode(', ', $idsDisponibles)
                ], 404);
            }
            
            // Verificar que ambos ciclos estén finalizados
            if (!$cicloA->fechaFin || $cicloA->fechaFin == '0000-00-00' || 
                !$cicloB->fechaFin || $cicloB->fechaFin == '0000-00-00') {
                return response()->json([
                    'success' => false,
                    'error' => 'Ambos ciclos deben estar finalizados'
                ], 400);
            }
            
            // Calcular datos según el tipo solicitado
            $datos = [];
            
            switch ($tipoDato) {
                case 'humedad_cama1':
                    $datos = $this->compararHumedadCama($cicloA, $cicloB, 'cama1');
                    break;
                case 'humedad_cama2':
                    $datos = $this->compararHumedadCama($cicloA, $cicloB, 'cama2');
                    break;
                case 'consumo_agua':
                    $datos = $this->compararConsumoAgua($cicloA, $cicloB, $tipoRiego);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Tipo de dato no válido'
                    ], 400);
            }
            
            return response()->json([
                'success' => true,
                'ciclo_a_nombre' => $cicloA->descripcion ?? 'Ciclo A',
                'ciclo_b_nombre' => $cicloB->descripcion ?? 'Ciclo B',
                'ciclo_a_fechas' => $cicloA->fechaInicio . ' al ' . $cicloA->fechaFin,
                'ciclo_b_fechas' => $cicloB->fechaInicio . ' al ' . $cicloB->fechaFin,
                'tipo_dato' => $tipoDato,
                'tipo_riego' => $tipoRiego,
                'datos' => $datos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al comparar ciclos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al comparar ciclos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Comparar humedad de una cama específica entre dos ciclos
     */
    private function compararHumedadCama($cicloA, $cicloB, $cama)
    {
        // Calcular duración de cada ciclo en días
        $duracionA = Carbon::parse($cicloA->fechaInicio)->diffInDays(Carbon::parse($cicloA->fechaFin));
        $duracionB = Carbon::parse($cicloB->fechaInicio)->diffInDays(Carbon::parse($cicloB->fechaFin));
        
        // Obtener datos de humedad normalizados a días del ciclo
        $humedadA = $this->obtenerHumedadNormalizada([], $cicloA->fechaInicio, $cicloA->fechaFin, $cama);
        $humedadB = $this->obtenerHumedadNormalizada([], $cicloB->fechaInicio, $cicloB->fechaFin, $cama);
        
        // Normalizar a la misma escala (máximo común de días)
        $maxDias = max($duracionA, $duracionB);
        
        $resultado = [
            'etiquetas' => [],
            'ciclo_a_datos' => [],
            'ciclo_b_datos' => [],
            'ciclo_a_promedio' => 0,
            'ciclo_b_promedio' => 0
        ];
        
        // Generar etiquetas para el eje X
        for ($dia = 1; $dia <= $maxDias; $dia++) {
            $resultado['etiquetas'][] = 'Día ' . $dia;
        }
        
        // Rellenar datos del ciclo A
        for ($dia = 1; $dia <= $maxDias; $dia++) {
            $resultado['ciclo_a_datos'][] = isset($humedadA[$dia]) ? round($humedadA[$dia], 2) : null;
        }
        
        // Rellenar datos del ciclo B
        for ($dia = 1; $dia <= $maxDias; $dia++) {
            $resultado['ciclo_b_datos'][] = isset($humedadB[$dia]) ? round($humedadB[$dia], 2) : null;
        }
        
        // Calcular promedios
        $resultado['ciclo_a_promedio'] = count($humedadA) > 0 ? round(array_sum($humedadA) / count($humedadA), 2) : 0;
        $resultado['ciclo_b_promedio'] = count($humedadB) > 0 ? round(array_sum($humedadB) / count($humedadB), 2) : 0;
        
        return $resultado;
    }
    
    /**
     * Obtener humedad normalizada por día del ciclo
     */
    private function obtenerHumedadNormalizada($cultivoIds, $fechaInicio, $fechaFin, $cama)
    {
        $fechaInicioCarbon = Carbon::parse($fechaInicio);
        $fechaFinCarbon = Carbon::parse($fechaFin);
        $duracion = $fechaInicioCarbon->diffInDays($fechaFinCarbon);
        
        $humedadPorDia = [];
        
        // Seleccionar el modelo correcto según la cama
        $modelo = $cama === 'cama1' ? CamaSiembra::class : Cama2::class;
        
        // Obtener lecturas agrupadas por fecha (sin filtrar por cultivoId)
        // ya que las tablas cama1 y cama2 no tienen esa columna
        $lecturas = $modelo::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->select(DB::raw('fecha, AVG(humedad) as promedio_humedad'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        // Convertir fechas a días del ciclo
        foreach ($lecturas as $lectura) {
            $fechaLectura = Carbon::parse($lectura->fecha);
            $diaDelCiclo = $fechaInicioCarbon->diffInDays($fechaLectura) + 1;
            
            if ($diaDelCiclo >= 1 && $diaDelCiclo <= $duracion) {
                $humedadPorDia[$diaDelCiclo] = $lectura->promedio_humedad;
            }
        }
        
        return $humedadPorDia;
    }
    
    /**
     * Comparar consumo de agua entre dos ciclos
     */
    private function compararConsumoAgua($cicloA, $cicloB, $tipoRiego)
    {
        // Obtener cultivos de cada ciclo
        $cultivosA = \App\Models\Cultivo::where('cicloId', $cicloA->cicloId)->pluck('cultivoId')->toArray();
        $cultivosB = \App\Models\Cultivo::where('cicloId', $cicloB->cicloId)->pluck('cultivoId')->toArray();
        
        // Calcular duración de cada ciclo
        $duracionA = Carbon::parse($cicloA->fechaInicio)->diffInDays(Carbon::parse($cicloA->fechaFin));
        $duracionB = Carbon::parse($cicloB->fechaInicio)->diffInDays(Carbon::parse($cicloB->fechaFin));
        
        $maxDias = max($duracionA, $duracionB);
        
        $resultado = [
            'etiquetas' => [],
            'ciclo_a_datos' => [],
            'ciclo_b_datos' => [],
            'ciclo_a_total' => 0,
            'ciclo_b_total' => 0,
            'tipo_riego' => $tipoRiego
        ];
        
        // Generar etiquetas
        for ($dia = 1; $dia <= $maxDias; $dia++) {
            $resultado['etiquetas'][] = 'Día ' . $dia;
        }
        
        // Obtener consumo por día según el tipo de riego
        switch ($tipoRiego) {
            case 'manual':
                $consumoA = $this->obtenerConsumoRiegoManual($cultivosA, $cicloA->fechaInicio, $cicloA->fechaFin);
                $consumoB = $this->obtenerConsumoRiegoManual($cultivosB, $cicloB->fechaInicio, $cicloB->fechaFin);
                break;
            case 'valvula':
                $consumoA = $this->obtenerConsumoValvula($cultivosA, $cicloA->fechaInicio, $cicloA->fechaFin);
                $consumoB = $this->obtenerConsumoValvula($cultivosB, $cicloB->fechaInicio, $cicloB->fechaFin);
                break;
            case 'total':
            default:
                $consumoManualA = $this->obtenerConsumoRiegoManual($cultivosA, $cicloA->fechaInicio, $cicloA->fechaFin);
                $consumoValvulaA = $this->obtenerConsumoValvula($cultivosA, $cicloA->fechaInicio, $cicloA->fechaFin);
                $consumoA = $this->sumarConsumos($consumoManualA, $consumoValvulaA);
                
                $consumoManualB = $this->obtenerConsumoRiegoManual($cultivosB, $cicloB->fechaInicio, $cicloB->fechaFin);
                $consumoValvulaB = $this->obtenerConsumoValvula($cultivosB, $cicloB->fechaInicio, $cicloB->fechaFin);
                $consumoB = $this->sumarConsumos($consumoManualB, $consumoValvulaB);
                break;
        }
        
        // Rellenar datos normalizados
        for ($dia = 1; $dia <= $maxDias; $dia++) {
            $resultado['ciclo_a_datos'][] = isset($consumoA[$dia]) ? round($consumoA[$dia], 2) : 0;
            $resultado['ciclo_b_datos'][] = isset($consumoB[$dia]) ? round($consumoB[$dia], 2) : 0;
        }
        
        // Calcular totales
        $resultado['ciclo_a_total'] = round(array_sum($consumoA), 2);
        $resultado['ciclo_b_total'] = round(array_sum($consumoB), 2);
        
        return $resultado;
    }
    
    /**
     * Obtener consumo de riego manual normalizado
     */
    private function obtenerConsumoRiegoManual($cultivoIds, $fechaInicio, $fechaFin)
    {
        if (empty($cultivoIds)) {
            return [];
        }
        
        $fechaInicioCarbon = Carbon::parse($fechaInicio);
        $duracion = $fechaInicioCarbon->diffInDays(Carbon::parse($fechaFin));
        
        $consumoPorDia = [];
        
        $registros = RiegoManual::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicio, $fechaFin])
            ->select(DB::raw('DATE(fechaEncendido) as fecha, SUM(volumen) as total_volumen'))
            ->groupBy(DB::raw('DATE(fechaEncendido)'))
            ->get();
        
        foreach ($registros as $registro) {
            $fechaRegistro = Carbon::parse($registro->fecha);
            $diaDelCiclo = $fechaInicioCarbon->diffInDays($fechaRegistro) + 1;
            
            if ($diaDelCiclo >= 1 && $diaDelCiclo <= $duracion) {
                $consumoPorDia[$diaDelCiclo] = $registro->total_volumen;
            }
        }
        
        return $consumoPorDia;
    }
    
    /**
     * Obtener consumo de válvula normalizado
     */
    private function obtenerConsumoValvula($cultivoIds, $fechaInicio, $fechaFin)
    {
        if (empty($cultivoIds)) {
            return [];
        }
        
        $fechaInicioCarbon = Carbon::parse($fechaInicio);
        $duracion = $fechaInicioCarbon->diffInDays(Carbon::parse($fechaFin));
        
        $consumoPorDia = [];
        
        $registros = Valvula::whereIn('cultivoId', $cultivoIds)
            ->whereBetween('fechaEncendido', [$fechaInicio, $fechaFin])
            ->select(DB::raw('DATE(fechaEncendido) as fecha, SUM(volumen) as total_volumen'))
            ->groupBy(DB::raw('DATE(fechaEncendido)'))
            ->get();
        
        foreach ($registros as $registro) {
            $fechaRegistro = Carbon::parse($registro->fecha);
            $diaDelCiclo = $fechaInicioCarbon->diffInDays($fechaRegistro) + 1;
            
            if ($diaDelCiclo >= 1 && $diaDelCiclo <= $duracion) {
                $consumoPorDia[$diaDelCiclo] = $registro->total_volumen;
            }
        }
        
        return $consumoPorDia;
    }
    
    /**
     * Sumar dos arrays de consumo por día
     */
    private function sumarConsumos($consumo1, $consumo2)
    {
        $resultado = [];
        $keys = array_unique(array_merge(array_keys($consumo1), array_keys($consumo2)));
        
        foreach ($keys as $dia) {
            $valor1 = isset($consumo1[$dia]) ? $consumo1[$dia] : 0;
            $valor2 = isset($consumo2[$dia]) ? $consumo2[$dia] : 0;
            $resultado[$dia] = $valor1 + $valor2;
        }
        
        return $resultado;
    }
}
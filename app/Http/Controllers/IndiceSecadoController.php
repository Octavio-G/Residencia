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
        $cama1 = $this->calcularDatosCama(CamaSiembra::class, 'Cama 1', 'Cilantro');
        $cama2 = $this->calcularDatosCama(Cama2::class, 'Cama 2', 'Rábano');

        return response()->json([
            'cama1' => $cama1,
            'cama2' => $cama2
        ]);
    }

    private function calcularDatosCama($modelo, $nombreCama, $tipoCultivo)
    {
        // 1. Obtener historial (sin cambios, tu lógica estaba bien)
        $lecturas = $modelo::orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->limit(20) // Aumenté a 20 para mejor gráfica
            ->get()
            ->map(function ($lectura) {
                return [
                    'fecha' => $lectura->fecha,
                    'hora' => $lectura->hora,
                    'humedad' => $lectura->humedad
                ];
            })
            ->reverse()
            ->values();

        // 2. Obtener datos actuales
        $lecturaReciente = $modelo::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
        $humedadActual = $lecturaReciente ? $lecturaReciente->humedad : 0;

        // 3. Obtener Temperatura (CRÍTICO para la fórmula)
        // Usamos la última registrada en general, ya que la temperatura ambiente afecta a ambas camas igual
        $temperaturaModel = Temperatura::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
        $temperaturaActual = $temperaturaModel ? $temperaturaModel->temperatura : 25; // Default 25°C

        // 4. Calcular Predicción Inteligente
        // Pasamos la temperatura actual para ajustar la velocidad
        $minutosRestantes = $this->calcularMinutosHastaSecado($humedadActual, $temperaturaActual);

        // 5. Determinar Mensajes y Estados
        $mensajeEstado = $this->obtenerMensajeEstado($humedadActual, $minutosRestantes, $temperaturaActual);

        return [
            'nombre' => $nombreCama,
            'cultivo' => $tipoCultivo,
            'humedad_actual' => $humedadActual,
            'temperatura_actual' => $temperaturaActual,
            'tiempo_restante' => [
                'horas' => intval($minutosRestantes / 60),
                'minutos' => $minutosRestantes % 60,
                'total_minutos' => $minutosRestantes // Útil para lógica JS
            ],
            'mensaje_estado' => $mensajeEstado,
            'lecturas_historial' => $lecturas
        ];
    }

    /**
     * Fórmula de Secado Hídrico con Ajuste Térmico
     * Basada en el principio de evapotranspiración simplificada.
     */
    private function calcularMinutosHastaSecado($humedadActual, $temperatura)
    {
        $limiteCritico = 30; // Tu límite rojo

        if ($humedadActual <= $limiteCritico) {
            return 0;
        }

        // --- LA FÓRMULA MÁGICA ---
        
        // 1. Velocidad Base: En un día templado (20-24°C), la tierra pierde aprox 0.5% a 0.8% por hora
        $velocidadBase = 0.6; 

        // 2. Factor Térmico (El Acelerador)
        // Si hay más de 25°C, aceleramos. Si hay menos, frenamos.
        // Por cada grado extra, aumentamos la velocidad un 10%
        $diferenciaTemp = $temperatura - 25; 
        $factorAceleracion = 1 + ($diferenciaTemp * 0.10); 

        // Limites de seguridad para el factor (para que no de negativo en fríos extremos)
        if ($factorAceleracion < 0.5) $factorAceleracion = 0.5; // Mínimo mitad de velocidad
        
        // 3. Velocidad Final Ajustada
        $tasaSecadoReal = $velocidadBase * $factorAceleracion;

        // Ejemplo: 
        // A 25°C -> Tasa = 0.6% por hora
        // A 35°C -> Tasa = 1.2% por hora (Se seca el doble de rápido)

        // 4. Cálculo de tiempo
        $humedadPerdidaNecesaria = $humedadActual - $limiteCritico;
        $horasRestantes = $humedadPerdidaNecesaria / $tasaSecadoReal;

        return intval($horasRestantes * 60);
    }

    private function obtenerMensajeEstado($humedadActual, $minutosRestantes, $temperatura)
    {
        // Prioridad 1: Humedad Crítica
        if ($humedadActual <= 30) {
            return "🔴 CRÍTICO: Suelo seco. Riego inmediato requerido.";
        }

        // Prioridad 2: Alerta de Ola de Calor (Nuevo)
        if ($temperatura >= 30 && $humedadActual < 50) {
            return "⚠️ ALERTA CALOR: Evaporación acelerada. Prepare riego.";
        }

        // Prioridad 3: Advertencia estándar
        if ($humedadActual <= 50) {
            return "🟡 ADVERTENCIA: Nivel bajo. Monitorear.";
        }

        return "🟢 ÓPTIMO: Niveles de humedad y temperatura adecuados.";
    }
}
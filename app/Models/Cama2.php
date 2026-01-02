<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cama2 extends Model
{
    protected $table = 'cama2';
    protected $primaryKey = 'idCama2';
    public $timestamps = false;
    
    protected $fillable = [
        'idCama2',
        'humedad',
        'temperatura',
        'fecha',
        'hora'
    ];
    
    protected $casts = [
        'humedad' => 'decimal:2',
        'fecha' => 'date',
        'hora' => 'string',
    ];
    
    // Método para obtener el estado de salud basado en humedad
    public function getEstadoSaludAttribute()
    {
        // Lógica de ponderación solo con humedad ya que es el único dato disponible
        $riesgoHumedad = 0;
        if ($this->humedad < 30) {
            $riesgoHumedad = 100; // Muy bajo
        } elseif ($this->humedad <= 50) {
            $riesgoHumedad = 70; // Bajo
        } elseif ($this->humedad > 100) {
            $riesgoHumedad = 100; // Muy alto (teóricamente no debería pasar)
        } elseif ($this->humedad > 60) {
            $riesgoHumedad = 0; // Óptimo
        } else {
            $riesgoHumedad = 0; // Óptimo (entre 50-60)
        }
        
        // Determinar color del semáforo
        if ($riesgoHumedad >= 80) {
            return 'rojo'; // Crítico
        } elseif ($riesgoHumedad >= 50) {
            return 'amarillo'; // Advertencia
        } else {
            return 'verde'; // Normal
        }
    }

    // Método para calcular tiempo estimado hasta el estrés hídrico
    public function getTiempoHastaEstresAttribute()
    {
        // Como no tenemos tasa de secado, usaremos un valor por defecto
        $umbralEstrés = 30; // Valor de humedad crítica
        $tasaSecado = 2.0; // % por hora (valor estimado)
        
        if ($this->humedad <= $umbralEstrés) {
            return 0; // Ya en estrés
        }
        
        return ($this->humedad - $umbralEstrés) / $tasaSecado;
    }
}
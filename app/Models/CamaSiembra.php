<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamaSiembra extends Model
{
    use HasFactory;

    // Especificar el nombre de la tabla existente
    protected $table = 'cama1'; // Asumiendo que empezamos con cama1
    
    // Especificar el nombre de la clave primaria
    protected $primaryKey = 'idCama1';

    // Los campos que pueden ser asignados masivamente
    protected $fillable = [
        'idCama1',
        'humedad',
        'temperatura',
        'fecha',
        'hora'
    ];

    // Los campos que deben ser convertidos a tipos nativos
    protected $casts = [
        'humedad' => 'decimal:2',
        'fecha' => 'date',
        'hora' => 'string',
    ];

    // Método para obtener el estado de salud basado en humedad
    public function getEstadoSaludAttribute()
    {
        // Lógica basada en humedad con umbrales claros
        if ($this->humedad <= 30) {
            return 'rojo'; // Crítico
        } elseif ($this->humedad > 30 && $this->humedad <= 60) {
            return 'amarillo'; // Advertencia
        } else {
            return 'verde'; // Óptimo
        }
    }

    // Método para calcular tiempo estimado hasta el estrés hídrico
    public function getTiempoHastaEstresAttribute()
    {
        // Umbral crítico de humedad
        $umbralEstrés = 30; // Valor de humedad crítica
        
        if ($this->humedad <= $umbralEstrés) {
            return 0; // Ya en estrés
        }
        
        // Calcular tasa de secado basada en temperatura (más realista)
        // Rango de 5-10% por día, convertido a porcentaje por hora
        $tasaBase = 7.0; // % por día (promedio entre 5 y 10)
        
        // Ajustar tasa según temperatura
        $temperatura = $this->temperatura;
        if ($temperatura > 30) {
            $factorTemperatura = 1.5; // Mayor tasa de secado con alta temperatura
        } elseif ($temperatura > 25) {
            $factorTemperatura = 1.2; // Temperatura moderadamente alta
        } else {
            $factorTemperatura = 1.0; // Temperatura normal
        }
        
        // Convertir tasa diaria a tasa horaria
        $tasaSecado = ($tasaBase * $factorTemperatura) / 24; // % por hora
        
        return ($this->humedad - $umbralEstrés) / $tasaSecado;
    }
}
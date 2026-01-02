<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temperatura extends Model
{
    use HasFactory;

    protected $table = 'temperatura';

    protected $primaryKey = 'idTemperatura';

    public $timestamps = false;

    protected $fillable = [
        'idTemperatura',
        'temperatura',
        'humedad',
        'fecha',
        'hora'
    ];

    protected $casts = [
        'temperatura' => 'decimal:2',
        'humedad' => 'decimal:2',
        'fecha' => 'date',
        'hora' => 'time',
    ];

    // Relación con el cultivo
    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivoId', 'cultivoId');
    }
}
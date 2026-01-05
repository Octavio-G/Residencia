<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cultivo extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cultivo';

    protected $primaryKey = 'cultivoId';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cultivoId',
        'nombreCultivo',
        'germinacion',
        'fechaSiembra',
        'fechaCosecha',
        'tipoRiego',
        'gramaje',
        'alturaMaxima',
        'alturaMinima',
        'temperaturaAmbienteMaxima',
        'temperaturaAmbienteMinima',
        'humedadAmbienteMaxima',
        'humedadAmbienteMinima',
        'humedadMinimaTierra',
        'presionBarometricaMaxima',
        'presionBarometricaMinima',
        'cicloId',
        'humedadMaximaTierra',
        'descripcion',
        'surcosSembrados',
        'especieId',
        'metodoRiegoId',
        'umbral_estres'
    ];

    protected $casts = [
        'fechaSiembra' => 'date',
        'fechaCosecha' => 'date',
        'temperaturaAmbienteMaxima' => 'decimal:2',
        'temperaturaAmbienteMinima' => 'decimal:2',
        'humedadAmbienteMaxima' => 'decimal:2',
        'humedadAmbienteMinima' => 'decimal:2',
        'humedadMinimaTierra' => 'decimal:2',
        'humedadMaximaTierra' => 'decimal:2',
        'presionBarometricaMaxima' => 'decimal:2',
        'presionBarometricaMinima' => 'decimal:2',
        'gramaje' => 'decimal:2',
        'alturaMaxima' => 'decimal:2',
        'alturaMinima' => 'decimal:2',
        'umbral_estres' => 'decimal:2',
    ];

    // Relación con el ciclo de siembra
    public function ciclo()
    {
        return $this->belongsTo(CicloSiembra::class, 'cicloId', 'cicloId');
    }
}
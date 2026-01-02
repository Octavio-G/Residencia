<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CicloSiembra extends Model
{
    use HasFactory;

    protected $table = 'ciclosiembra';

    protected $primaryKey = 'cicloId';

    public $timestamps = false;

    protected $fillable = [
        'cicloId',
        'descripcion',
        'fechaInicio',
        'fechaFin',
        'ciclo'
    ];

    protected $casts = [
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
    ];

    // Relación con los cultivos
    public function cultivos()
    {
        return $this->hasMany(Cultivo::class, 'cicloId', 'cicloId');
    }
}
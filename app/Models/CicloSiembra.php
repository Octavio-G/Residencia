<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CicloSiembra extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ciclosiembra';

    protected $primaryKey = 'cicloId';

    public $incrementing = false;

    protected $keyType = 'string';

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
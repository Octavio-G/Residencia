<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valvula extends Model
{
    use HasFactory;

    protected $table = 'valvula';

    protected $primaryKey = 'idValvula';

    public $timestamps = false;

    protected $fillable = [
        'idValvula',
        'fechaEncendido',
        'fechaApagado',
        'volumen',
        'cultivoId'
    ];

    protected $casts = [
        'fechaEncendido' => 'datetime',
        'fechaApagado' => 'datetime',
        'volumen' => 'decimal:3',
    ];
}
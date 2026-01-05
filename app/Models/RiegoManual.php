<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiegoManual extends Model
{
    use HasFactory;

    protected $table = 'riegomanual';

    protected $primaryKey = 'idRiegoManual';

    public $timestamps = false;

    protected $fillable = [
        'idRiegoManual',
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
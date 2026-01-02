<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    // Especificar la tabla existente
    protected $table = 'rol';

    // Especificar la clave primaria
    protected $primaryKey = 'id';

    // Desactivar timestamps si no existen en la tabla
    public $timestamps = false;

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'rol_nombre',
        'rol_valor'
    ];

    // Relación con usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id', 'id');
    }
}
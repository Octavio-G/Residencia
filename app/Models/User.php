<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Especificar la tabla existente
    protected $table = 'user';

    // Especificar la clave primaria
    protected $primaryKey = 'id';

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'rol_id',
        'estado_id',
        'tipo_usuario_id',
        'auth_key',
        'password_reset_token',
        'verification_token',
        'created_at',
        'updated_at'
    ];

    // Campos que deben ser ocultados en serialización
    protected $hidden = [
        'password_hash',
        'auth_key',
        'password_reset_token',
        'verification_token',
    ];

    // Casts para atributos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Getter para el campo de contraseña (para compatibilidad con Laravel Auth)
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    
    /**
     * Establecer la contraseña hash al guardar
     */
    public function setPasswordHashAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
    }
}
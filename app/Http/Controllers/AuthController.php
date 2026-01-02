<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar inicio de sesión
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario por nombre de usuario
        $user = User::where('username', $request->username)->first();

        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && Hash::check($request->password, $user->password_hash)) {
            // Verificar si el usuario está activo (asumiendo que estado_id = 1 es activo)
            if ($user->estado_id == 1) {
                Auth::login($user);
                return redirect()->intended('/bi');
            } else {
                return back()->withErrors([
                    'username' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Registrar un nuevo usuario
     */
    public function register(StoreUserRequest $request)
    {
        // Crear el nuevo usuario
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'rol_id' => 2, // Asignar rol de usuario por defecto (ajusta según tus necesidades)
            'estado_id' => 1, // Activar cuenta por defecto
            'tipo_usuario_id' => 1, // Tipo de usuario por defecto (ajusta según tus necesidades)
            'auth_key' => \Illuminate\Support\Str::random(32),
        ]);

        // Iniciar sesión automáticamente después del registro
        Auth::login($user);

        return redirect('/bi')->with('success', '¡Registro exitoso! Bienvenido al sistema.');
    }
}
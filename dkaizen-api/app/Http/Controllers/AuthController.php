<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
// Método para crear una cuenta (Cliente / Admin)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|string|in:admin,barbero,cliente' // Permitimos que nos digan qué rol es
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'cliente' // Si no envían rol, es cliente por defecto
        ]);
        
        
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // Método para Iniciar Sesión
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Correo o contraseña incorrectos',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => Auth::guard('api')->user()
        ]);
    }

    // Método para Cerrar Sesión
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    // ----------------------------------------------------
    // INICIO DE SESIÓN CON GOOGLE (Atajo VIP)
    // ----------------------------------------------------
    public function loginWithGoogle(Request $request)
    {
        // 1. Exigimos que el frontend nos mande el token de Google
        $request->validate([
            'token' => 'required|string', 
        ]);

        try {
            // 2. Llamamos al guardia de Google para verificar el token
            $googleClient = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $googleClient->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json([
                    'success' => false, 
                    'message' => 'El token de Google es inválido o expiró.'
                ], 401);
            }

            // 3. Si Google dice que es real, sacamos sus datos
            $email = $payload['email'];
            $name = $payload['name'];

            // 4. Buscamos si el tigre ya es cliente de la barbería
            $user = User::where('email', $email)->first();

            // Si es nuevo, lo registramos en automático
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Clave fantasma
                    'role' => 'cliente' // Mantenemos tu rol por defecto
                ]);
            }

            // 5. ¡Le damos nuestra propia manilla VIP usando tu mismo método!
            $token = Auth::guard('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Bienvenido, sesión iniciada con Google.',
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al validar con Google.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
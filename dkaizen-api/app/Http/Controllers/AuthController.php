<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 1. REGISTRO MANUAL
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|string|in:admin,barbero,cliente'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'cliente'
        ]);
        
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // 2. LOGIN MANUAL
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

    // 3. LOGOUT
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['success' => true, 'message' => 'Sesión cerrada']);
    }

    // 4. LOGIN CON GOOGLE (CONEXIÓN SEGURA)
    public function loginWithGoogle(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            // Configuramos el cliente de Google
            $googleClient = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $googleClient->setAccessToken($request->token);
            
            // Pedimos la info del usuario
            $googleService = new \Google\Service\Oauth2($googleClient);
            $userinfo = $googleService->userinfo->get();

            if (!$userinfo || !$userinfo->email) {
                return response()->json(['success' => false, 'message' => 'No se pudo obtener info de Google.'], 401);
            }

            $email = $userinfo->email;
            $name = $userinfo->name;

            // Buscamos o creamos al usuario
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'cliente'
                ]);
            }

            // Generamos tu token VIP
            $token = Auth::guard('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Sesión iniciada con Google',
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
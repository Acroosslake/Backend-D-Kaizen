<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 1. REGISTRO MANUAL
    public function register(Request $request)
    {
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|string|in:admin,barbero,client'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            // ✅ Lo marcamos como verificado automáticamente para evitar el bloqueo en el login
            'email_verified_at' => now(), 
        ]);
        
        // 🚀 Ya no enviamos el correo, el usuario entra directo a la app.
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente.'
        ], 201);
    }

    // 2. LOGIN MANUAL
    public function login(Request $request)
    {
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

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

        $user = Auth::guard('api')->user();

        // 🛡️ BARRERA DE SEGURIDAD COMENTADA: Ya no bloqueamos si no verificó el correo
        /*
        if (!$user->hasVerifiedEmail()) {
            Auth::guard('api')->logout(); 
            return response()->json([
                'success' => false,
                'message' => 'Debes verificar tu correo...',
            ], 403);
        }
        */

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    // 3. LOGOUT
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['success' => true, 'message' => 'Sesión cerrada']);
    }

    // 4. LOGIN CON GOOGLE OPTIMIZADO
    public function loginWithGoogle(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $googleResponse = \Illuminate\Support\Facades\Http::withToken($request->token)
                                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($googleResponse->failed()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'El token de Google no es válido o ha expirado.'
                ], 401);
            }

            $userinfo = $googleResponse->object();

            if (!isset($userinfo->email)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se pudo obtener el correo de Google.'
                ], 400);
            }

            $user = User::where('email', strtolower($userinfo->email))->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userinfo->name,
                    'email' => strtolower($userinfo->email),
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'client',
                    'email_verified_at' => now() 
                ]);
            }

            $token = Auth::guard('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Sesión iniciada con Google exitosamente.',
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno en el servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 5. ACTUALIZAR PERFIL
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Perfil actualizado con éxito, fiera!',
            'user' => $user
        ]);
    }

    /**
     * Enviar enlace de recuperación de contraseña
     */
    public function forgotPassword(Request $request)
    {
        // 🔥 PARCHE: Convertimos el correo a minúsculas
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

        // 1. Validamos que nos manden un correo válido
        $request->validate(['email' => 'required|email']);

        // 2. Laravel busca el correo y le genera un Token de seguridad
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // 3. Respondemos dependiendo de si funcionó o no
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => '¡Te hemos enviado un enlace de recuperación a tu correo!'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No pudimos encontrar ningún usuario con ese correo electrónico.'
        ], 400);
    }

    /**
     * Procesar el cambio de contraseña con el token
     */
    public function resetPassword(Request $request)
    {
        // 🔥 PARCHE: Convertimos el correo a minúsculas
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' exige que envíes 'password_confirmation'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu contraseña ha sido restablecida con éxito! Ya puedes iniciar sesión.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'El token es inválido o ha expirado. Por favor, solicita uno nuevo.'
        ], 400);
    }
}
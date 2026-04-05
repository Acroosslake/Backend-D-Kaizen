<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // 1. Enviar el correo con el link de recuperación
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // USAMOS LA URL DE TU FRONTEND EN CODESPACES (La misma que usas para entrar a la app)
        // Agrégala a tu .env de Laravel como APP_FRONTEND_URL
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:5173') . "/restablecer-contrasena?token=" . $token . "&email=" . $request->email;

        // Cuerpo del mensaje un poco más "D'KAIZEN"
        $mensaje = "Hola, bro.\n\nRecibimos una solicitud para cambiar tu contraseña en D'KAIZEN BARBER.\n\nHaz clic en el siguiente enlace para crear una nueva clave:\n\n" . $frontendUrl . "\n\nEste enlace expirará en 60 minutos.\n\nSi no solicitaste este cambio, puedes ignorar este correo.";

        Mail::raw($mensaje, function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Restablece tu contraseña | D\'KAIZEN');
        });

        return response()->json([
            'success' => true, 
            'message' => '¡Enviado! Revisa tu Gmail, ahí están las instrucciones.'
            ]
        );

        // Simulamos la URL de tu futuro Frontend (ej: http://localhost:3000)
        $frontendUrl = env('APP_FRONTEND_URL') . "/reset-password?token=" . $token . "&email=" . $request->email;

        // Enviamos el correo usando Mailtrap
        Mail::raw("Hola tigre,\n\nPara recuperar tu contraseña en la Barbería D-Kaizen, haz clic en el siguiente enlace:\n\n" . $frontendUrl . "\n\nSi no fuiste tú, ignora este correo de forma segura.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Recuperación de Contraseña - Barbería D-Kaizen');
        });

        return response()->json([
            'success' => true, 
            'message' => 'Correo de recuperación enviado con éxito. Revisa tu bandeja de entrada.'
        ]);
    }

    // 2. Recibir el token y la nueva contraseña para guardarla
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed', // Exige que envíen password_confirmation
        ]);

        // Buscamos si el token es válido y le pertenece a ese correo
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json(['success' => false, 'message' => 'Token inválido o correo incorrecto.'], 400);
        }

        // Actualizamos la clave del usuario
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Borramos el token para que no se pueda usar dos veces
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Contraseña actualizada con éxito. Ya puedes iniciar sesión.'
        ]);
    }
}
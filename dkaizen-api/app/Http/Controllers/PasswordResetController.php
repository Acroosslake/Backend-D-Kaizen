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
            'email' => 'required|email|exists:users,email' // Validamos que el correo exista en la BD
        ]);

        $token = Str::random(60); // Generamos el código secreto

        // Guardamos el token en la tabla que Laravel trae por defecto para esto
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Simulamos la URL de tu futuro Frontend (ej: http://localhost:3000)
        $frontendUrl = "http://localhost:3000/reset-password?token=" . $token . "&email=" . $request->email;

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
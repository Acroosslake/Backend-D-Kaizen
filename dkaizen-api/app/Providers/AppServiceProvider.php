<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword; // 🌟 IMPORTACIÓN NUEVA
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1️⃣ Interceptamos el correo de verificación por defecto de Laravel
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            
            // Extraemos los parámetros de seguridad (?expires=...&signature=...) de la URL original
            $parsedUrl = parse_url($url);
            $queryString = $parsedUrl['query'] ?? '';
            
            // Redireccionamos el enlace hacia tu frontend en React
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $customUrl = $frontendUrl . '/verificar-correo?' . $queryString;
            
            // Extraemos el primer nombre del usuario para el saludo personalizado
            $userName = explode(' ', $notifiable->name)[0];

            // Retornamos la vista HTML estructurada en blade
            return (new MailMessage)
                ->subject("¡Bienvenido a D'Kaizen Barber! Verifica tu cuenta ✂️🔥")
                ->view('emails.verify', [
                    'url' => $customUrl,
                    'name' => $userName
                ]);
        });

        // 2️⃣ 🌟 NUEVO: Interceptamos el correo de Recuperación de Contraseña con diseño Premium
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            
            // Armamos la URL hacia el frontend
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $customUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . $notifiable->getEmailForPasswordReset();
            
            // Extraemos el nombre (por si acaso el objeto no lo tiene, ponemos 'Cliente')
            $userName = isset($notifiable->name) ? explode(' ', $notifiable->name)[0] : 'Cliente';

            return (new MailMessage)
                ->subject("Recuperación de Contraseña - D'Kaizen Barber ✂️🔥")
                ->view('emails.reset', [
                    'url' => $customUrl,
                    'name' => $userName
                ]);
        });
    }
}
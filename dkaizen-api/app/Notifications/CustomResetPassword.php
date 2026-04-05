<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    // El constructor recibe el token que genera Laravel
    public function __construct($token)
    {
        $this->token = $token;
    }

    // Le decimos a Laravel que use el canal de 'mail'
    public function via($notifiable)
    {
        return ['mail'];
    }

    // Aquí es donde sucede la magia del diseño
    public function toMail($notifiable)
    {
        // Construimos la URL con el token y el email
        $url = url(config('app.frontend_url').'/reset-password?token='.$this->token.'&email='.$notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(' Restablece tu contraseña | D\'KAIZEN')
            ->greeting('¡Hola, fiera!')
            ->line('Recibimos una solicitud para cambiar la clave de tu cuenta en D\'KAIZEN BARBER.')
            ->action('RESTABLECER CONTRASEÑA', $url)
            ->line('Si no pediste esto, ignora este correo y sigue luciendo ese buen corte.')
            ->salutation('¡Nos vemos en la silla, bro!');
    }
}
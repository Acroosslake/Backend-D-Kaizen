<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - D'Kaizen</title>
</head>
<body style="background-color: #030303; color: #ffffff; font-family: 'Helvetica Neue', Arial, sans-serif; padding: 40px 10px; margin: 0; text-align: center;">
    
    <div style="max-width: 500px; margin: 0 auto; background-color: #0a0a0a; border: 1px solid #1f2937; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <div style="padding: 40px 20px; border-bottom: 1px solid #1f2937; background-image: linear-gradient(to bottom, #030303, #0a0a0a);">
            <h1 style="color: #d4af37; font-size: 28px; letter-spacing: 5px; margin: 0; text-transform: uppercase; font-weight: normal;">D'KAIZEN</h1>
            <p style="color: #bd0003; font-size: 10px; letter-spacing: 3px; text-transform: uppercase; margin-top: 10px; font-weight: bold;">Acceso de Seguridad</p>
        </div>
        
        <div style="padding: 40px 30px;">
            <h2 style="font-size: 22px; font-weight: 300; margin-bottom: 20px; color: #ffffff;">
                Hola, <span style="color: #d4af37; font-style: italic;">{{ $name }}</span>
            </h2>
            
            <p style="color: #9ca3af; line-height: 1.6; font-size: 14px; margin-bottom: 40px;">
                Recibimos una solicitud para restablecer la contraseña de tu cuenta en D'Kaizen Barber. Si fuiste tú, haz clic en el botón dorado de abajo para crear una nueva clave y volver a agendar tus cortes.
            </p>
            
            <a href="{{ $url }}" style="background-color: #d4af37; color: #000000; padding: 16px 32px; text-decoration: none; font-weight: bold; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; border-radius: 50px; display: inline-block;">
                Cambiar mi Contraseña
            </a>
            
            <p style="color: #6b7280; font-size: 11px; margin-top: 40px; line-height: 1.5;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                <a href="{{ $url }}" style="color: #d4af37; word-break: break-all; text-decoration: none; margin-top: 10px; display: block;">{{ $url }}</a>
            </p>
        </div>
        
        <div style="background-color: #000000; padding: 20px; text-align: center; border-top: 1px solid #1f2937;">
            <p style="color: #4b5563; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                Si no solicitaste este cambio, puedes ignorar y eliminar este correo de forma segura. Tu cuenta sigue protegida.
            </p>
        </div>

    </div>

</body>
</html>
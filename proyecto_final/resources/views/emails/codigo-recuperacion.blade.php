<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #F0F2F5;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #010C67;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .header p {
            color: rgba(255,255,255,0.75);
            margin: 8px 0 0;
            font-size: 14px;
        }
        .body {
            padding: 36px 40px;
        }
        .body p {
            color: #444;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 16px;
        }
        .code-container {
            background: #F4F6FB;
            border: 2px dashed #010C67;
            border-radius: 10px;
            text-align: center;
            padding: 24px 20px;
            margin: 24px 0;
        }
        .code-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
            display: block;
        }
        .code {
            font-size: 42px;
            font-weight: 700;
            color: #010C67;
            letter-spacing: 12px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            font-size: 13px;
            color: #67000F;
            font-weight: 600;
            text-align: center;
            margin-top: 0;
        }
        .divider {
            border: none;
            border-top: 1px solid #EEEEEE;
            margin: 28px 0;
        }
        .footer {
            background-color: #F8F9FA;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #EEEEEE;
        }
        .footer p {
            font-size: 12px;
            color: #999;
            margin: 4px 0;
        }
        .footer strong {
            color: #010C67;
        }
        .warning-box {
            background: #FFF8E1;
            border-left: 4px solid #FFB75B;
            border-radius: 4px;
            padding: 12px 16px;
            margin-top: 16px;
        }
        .warning-box p {
            font-size: 13px;
            color: #555;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <h1>Génesis Profesional</h1>
            <p>Sistema de Gestión de Pasantías · UGB</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p>Hola,</p>
            <p>
                Recibimos una solicitud para restablecer la contraseña asociada al correo
                <strong>{{ $correoDestino }}</strong>.
                Usa el siguiente código de verificación:
            </p>

            <div class="code-container">
                <span class="code-label">Tu código de verificación</span>
                <div class="code">{{ $codigo }}</div>
            </div>

            <p class="expiry">⏱ Este código expira en <strong>15 minutos</strong>.</p>

            <hr class="divider">

            <div class="warning-box">
                <p>
                    🔒 Si <strong>no solicitaste</strong> este código, ignora este correo.
                    Tu contraseña <strong>no será cambiada</strong> a menos que ingreses el código.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Génesis Profesional</strong> · Universidad Gerardo Barrios</p>
            <p>Este es un correo automático, por favor no respondas.</p>
        </div>

    </div>
</body>
</html>

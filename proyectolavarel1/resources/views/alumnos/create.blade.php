<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Alumnos - Panel Administrativo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; /* Indigo */
            --primary-hover: #4f46e5;
            --bg-color: #0f172a; /* Slate 900 for modern dark mode */
            --glass-bg: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc; /* Slate 50 */
            --text-muted: #94a3b8; /* Slate 400 */
            --input-bg: rgba(15, 23, 42, 0.6);
            --input-border: rgba(99, 102, 241, 0.3);
            --input-focus: rgba(99, 102, 241, 0.8);
            --error: #ef4444;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 600px;
            perspective: 1000px;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 300;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        input {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 1rem;
            color: var(--text-main);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        input:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2), inset 0 2px 4px rgba(0,0,0,0.1);
            background: rgba(15, 23, 42, 0.8);
        }

        input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        /* Float Label Effect implicitly on focus */
        .form-group:focus-within label {
            color: var(--primary);
        }

        .btn-submit {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-submit:hover::after {
            left: 100%;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .alert {
            grid-column: 1 / -1;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 1rem;
            animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
        }
        
        .alert ul {
            margin-top: 0.5rem;
            margin-left: 1.5rem;
            font-weight: 400;
            font-size: 0.9rem;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Micro-interactions: Pulse effect on input fields on load */
        @keyframes pulseBorder {
            0% { border-color: var(--input-border); }
            50% { border-color: rgba(99, 102, 241, 0.5); }
            100% { border-color: var(--input-border); }
        }

        .form-group:nth-child(1) input { animation: pulseBorder 2s ease 1s 1; }

        /* Responsive */
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .glass-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="margin-bottom: 1rem;">
            <a href="{{ route('dashboard') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                &larr; Cerrar Formulario y Volver
            </a>
        </div>
        <div class="glass-card">
            <div class="header">
                <h1>Nuevo Alumno</h1>
                <p>Ingresa los datos para registrar un nuevo integrante</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>⚠️ Por favor corrige los siguientes errores:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('alumnos.store') }}" method="POST" class="form-grid">
                @csrf
                
                <div class="form-group" style="animation-delay: 0.1s;">
                    <label for="codigo">Código Estudiantil</label>
                    <input type="text" id="codigo" name="codigo" placeholder="Ej: AL-2026" required value="{{ old('codigo') }}">
                </div>

                <div class="form-group full-width" style="animation-delay: 0.2s;">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: José Chavarría" required value="{{ old('nombre') }}">
                </div>

                <div class="form-group" style="animation-delay: 0.3s;">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" placeholder="+503 7000-0000" value="{{ old('telefono') }}">
                </div>

                <div class="form-group" style="animation-delay: 0.4s;">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required value="{{ old('email') }}">
                </div>
                
                <div class="form-group full-width" style="animation-delay: 0.5s;">
                    <label for="direccion">Dirección Residencial</label>
                    <input type="text" id="direccion" name="direccion" placeholder="Ingresa la dirección completa" value="{{ old('direccion') }}">
                </div>

                <button type="submit" class="btn-submit">Registrar Alumno </button>
            </form>
        </div>
    </div>
</body>
</html>

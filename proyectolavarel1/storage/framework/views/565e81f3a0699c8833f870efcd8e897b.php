<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-color: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
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
            flex-direction: column;
            align-items: center;
            padding: 3rem 2rem;
            overflow-x: hidden;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            animation: slideDown 0.8s ease forwards;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            width: 100%;
            max-width: 1000px;
        }

        .module-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
        }

        .module-card:nth-child(1) { animation-delay: 0.1s; }
        .module-card:nth-child(2) { animation-delay: 0.2s; }
        .module-card:nth-child(3) { animation-delay: 0.3s; }
        .module-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .module-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
        }

        .module-card h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .module-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.3);
            border-color: rgba(99, 102, 241, 0.5);
            background: rgba(30, 41, 59, 0.8);
        }

        .module-card:hover .module-icon {
            transform: scale(1.1);
        }

        .btn-logout {
            margin-top: 4rem;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.2);
        }

        @media (max-width: 768px) {
            .header h1 { font-size: 2.2rem; }
            .dashboard-grid { gap: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema Académico</h1>
        <p>Selecciona un módulo para gestionar tu información</p>
    </div>

    <div class="dashboard-grid">
        <a href="<?php echo e(route('alumnos.create')); ?>" class="module-card">
            <i class="fa-solid fa-user-graduate module-icon"></i>
            <h2>Alumnos</h2>
            <p>Control y registro de estudiantes</p>
        </a>

        <a href="<?php echo e(route('docentes.create')); ?>" class="module-card">
            <i class="fa-solid fa-chalkboard-user module-icon"></i>
            <h2>Docentes</h2>
            <p>Directorio y gestión del profesorado</p>
        </a>

        <a href="<?php echo e(route('materias.create')); ?>" class="module-card">
            <i class="fa-solid fa-book module-icon"></i>
            <h2>Materias</h2>
            <p>Asignaturas y oferta académica</p>
        </a>

        <a href="<?php echo e(route('matriculas.create')); ?>" class="module-card">
            <i class="fa-solid fa-id-card module-icon"></i>
            <h2>Matrículas</h2>
            <p>Inscripciones y cursos asignados</p>
        </a>

        <a href="<?php echo e(route('inscripciones.create')); ?>" class="module-card">
            <i class="fa-solid fa-file-signature module-icon"></i>
            <h2>Inscripciones</h2>
            <p>Relación de alumnos y materias</p>
        </a>
    </div>

</body>
</html>
<?php /**PATH C:\Users\josec\OneDrive\Escritorio\Progra-IV-semi-2026\proyectolavarel1\resources\views/dashboard.blade.php ENDPATH**/ ?>
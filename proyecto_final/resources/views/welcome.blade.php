<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génesis Profesional - Inicio</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts: Lora e Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Lora:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* =========================================
           SISTEMA DE DISEÑO - GÉNESIS PROFESIONAL
           ========================================= */
        :root {
            --color-primario: #010C67;
            --color-secundario-1: #00589B;
            --gris-3: #DEDCDC;
            --fuente-titulo: 'Lora', serif;
            --fuente-cuerpo: 'Inter', sans-serif;
            --sombra-genesis: 0px 4px 4px -2px rgba(0, 0, 0, 0.67);
        }

        body {
            font-family: var(--fuente-cuerpo);
            background-color: #F8F9FA; 
            color: #000;
            overflow-x: hidden;
        }

        .header-top {
            border-bottom: 2px solid var(--gris-3);
            padding: 24px 0 16px 0;
            margin-bottom: 16px;
        }

        .titulo-principal {
            font-family: var(--fuente-titulo);
            font-size: 32px;
            font-weight: 500;
            color: #000;
            margin: 0;
        }

        .nav-link-custom {
            font-family: var(--fuente-cuerpo);
            font-size: 16px;
            font-weight: 500;
            color: #333;
            text-decoration: none;
            margin-right: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .nav-link-custom:hover {
            color: var(--color-primario);
            background-color: #EBEBEB;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .btn-genesis {
            width: 222px;
            height: 61px;
            border-radius: 18px;
            font-family: var(--fuente-titulo);
            font-size: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--sombra-genesis);
            border: none;
            transition: transform 0.2s, background-color 0.2s;
            text-decoration: none;
        }

        .btn-primario {
            background-color: var(--color-primario);
            color: white;
        }

        .btn-primario:hover {
            background-color: var(--color-secundario-1);
            color: white;
            transform: translateY(-2px);
        }

        .parrafo-hero {
            font-family: var(--fuente-cuerpo);
            font-size: 20px;
            font-weight: 400;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .img-hero {
            width: 100%;
            height: auto;
            border-radius: 4px;
            box-shadow: var(--sombra-genesis);
            object-fit: cover;
            max-height: 350px;
        }

        .espacio-superior-texto { margin-top: 80px; }
        .espacio-superior-img { margin-top: 48px; }
        
        .slider-controls {
            background-color: #6C6C6C;
            border-radius: 12px;
            display: inline-flex;
            padding: 8px 16px;
            gap: 16px;
            margin-top: -24px;
            position: relative;
            z-index: 10;
        }
        
        .slider-controls i {
            color: white;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.2s;
        }
        
        .slider-controls i:hover {
            color: #FFB75B; 
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="container-fluid px-5 header-top">
        <h1 class="titulo-principal">Génesis Profesional</h1>
    </div>

    <!-- Navegación -->
    <nav class="container-fluid px-5 d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex flex-wrap align-items-center">
            <a href="#" class="nav-link-custom">Empresas socias</a>
            <a href="#" class="nav-link-custom">Áreas de pasantías</a>
            <a href="#" class="nav-link-custom">Requisitos</a>
            <a href="#" class="nav-link-custom">Alcance</a>
        </div>
        <div class="d-flex gap-4">
            <!-- Redirección con parámetro ?vista=registro para abrir directamente el formulario de registro -->
            <a href="/login?vista=registro" class="btn-genesis btn-primario">Registrarse</a>
            <a href="/login" class="btn-genesis btn-primario">Iniciar sesión</a>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="container-fluid px-5 mb-5 pb-5">
        <div class="row">
            <div class="col-lg-5 pe-lg-5 d-flex flex-column justify-content-between">
                <div class="espacio-superior-texto pe-4">
                    <p class="parrafo-hero">
                        Conectamos el talento técnico con las empresas líderes del sector tecnológico para transformar el aprendizaje en experiencia profesional real.
                    </p>
                </div>
                <div class="espacio-superior-img mt-auto pt-5">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Equipo trabajando" class="img-hero">
                </div>
            </div>

            <div class="col-lg-6 offset-lg-1 ps-lg-4 d-flex flex-column justify-content-between">
                <div>
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Mentoría en tecnología" class="img-hero">
                </div>
                <div class="espacio-superior-texto mt-auto pt-5 ps-4">
                    <p class="parrafo-hero">
                        Génesis Profesional es el puente entre tus estudios y tu primera experiencia en el mundo laboral. Encuentra la pasantía ideal y potencia tus habilidades.
                    </p>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center">
                <div class="slider-controls shadow">
                    <i class="bi bi-chevron-left"></i>
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
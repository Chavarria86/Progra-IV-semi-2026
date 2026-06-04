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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Lora:wght@400;500&display=swap" rel="stylesheet">

    <!-- Vue 3 Global CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <style>
        :root {
            --color-ugb-blue: #000B58; /* Azul UGB */
            --color-text-dark: #000000;
            --color-bg-light: #F4F4F4; /* Fondo Gris */
            
            --font-title: 'Lora', serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--color-bg-light);
            color: var(--color-text-dark);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Header / Navbar */
        .navbar-ugb {
            background-color: var(--color-ugb-blue);
            padding: 20px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand-ugb {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #ffffff;
        }

        .navbar-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .nav-item-ugb {
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 400;
            opacity: 0.9;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-item-ugb:hover {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item-ugb.active-tab {
            background-color: #6D7CA8; /* Fondo destacado de pestaña activa */
            opacity: 1;
        }

        .nav-actions {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .btn-register-ugb {
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-register-ugb:hover {
            opacity: 0.8;
            color: #ffffff;
        }

        .btn-login-ugb {
            border: 1px solid #ffffff;
            border-radius: 18px;
            color: #ffffff;
            background-color: transparent;
            padding: 8px 24px;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-login-ugb:hover {
            background-color: #ffffff;
            color: var(--color-ugb-blue);
        }

        /* Container */
        .main-container {
            max-width: 1300px;
            margin: 64px auto;
            padding: 0 48px;
            min-height: 500px;
        }

        /* General Rows */
        .hero-row {
            display: flex;
            align-items: center;
            margin-bottom: 72px;
            gap: 60px;
        }

        .hero-text-block {
            flex: 1;
        }

        .hero-title {
            font-family: var(--font-title);
            font-size: 32px;
            font-weight: 500;
            line-height: 1.45;
            color: var(--color-text-dark);
            margin: 0;
        }

        .hero-image-block {
            flex: 1.1;
        }

        .hero-img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.08);
            object-fit: cover;
            display: block;
        }

        /* Asymmetric Blocks */
        .asymmetric-block {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0px 10px 30px rgba(0,0,0,0.04);
            display: flex;
            align-items: stretch;
            overflow: hidden;
            margin-bottom: 48px;
        }

        .asymmetric-block .block-text {
            flex: 1;
            padding: 48px;
            display: flex;
            align-items: center;
        }

        .asymmetric-block .block-img-container {
            flex: 1;
        }

        .asymmetric-block .block-img {
            width: 100%;
            height: 100%;
            min-height: 320px;
            object-fit: cover;
            display: block;
        }

        /* Requisitos List */
        .requisito-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .requisito-list li {
            font-size: 18px;
            line-height: 1.6;
            position: relative;
            padding-left: 24px;
        }

        .requisito-list li::before {
            content: "•";
            color: #000000;
            font-size: 28px;
            position: absolute;
            left: 0;
            top: -2px;
        }

        /* Slider Cards */
        .slider-wrapper {
            display: flex;
            align-items: center;
            gap: 24px;
            position: relative;
        }

        .slider-cards-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            width: 100%;
        }

        .card-ds {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0px 8px 24px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-ds-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-ds-body {
            padding: 24px;
            font-size: 15px;
            line-height: 1.6;
            color: #333333;
        }

        .slider-btn {
            background: none;
            border: none;
            font-size: 36px;
            color: #000000;
            cursor: pointer;
            transition: transform 0.2s;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-btn:hover {
            transform: scale(1.1);
        }

        /* Footer */
        .footer-ugb {
            background-color: var(--color-ugb-blue);
            color: #ffffff;
            padding: 60px 0;
            margin-top: 100px;
        }

        .footer-logo-section {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .footer-social-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 12px 0;
            letter-spacing: 0.5px;
        }

        .footer-social-icons {
            display: flex;
            gap: 16px;
        }

        .footer-social-icon {
            color: #ffffff;
            font-size: 24px;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .footer-social-icon:hover {
            opacity: 0.8;
            color: #ffffff;
        }

        .footer-contact-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .footer-contact-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 16px;
            opacity: 0.9;
        }

        .footer-contact-item i {
            font-size: 20px;
        }

        .footer-inner {
            padding: 0 48px;
        }

        /* Animations */
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.25s ease;
        }
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-ugb {
                padding: 20px 24px;
                flex-direction: column;
                gap: 20px;
            }
            .navbar-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
            }
            .main-container {
                margin: 24px auto;
                padding: 0 20px;
            }
            .hero-row {
                flex-direction: column;
                gap: 32px;
                margin-bottom: 48px;
            }
            .hero-row.reverse-mobile {
                flex-direction: column-reverse;
            }
            .asymmetric-block {
                flex-direction: column !important;
            }
            .asymmetric-block .block-text {
                padding: 24px;
            }
            .slider-cards-row {
                grid-template-columns: 1fr;
            }
            .footer-ugb {
                padding: 40px 0;
                margin-top: 60px;
            }
            .footer-inner {
                padding: 0 20px !important;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 24px;
            }
            .navbar-brand-ugb img {
                height: 60px !important;
            }
        }
    </style>
</head>
<body>

    <div id="app">
        <!-- Header / Barra de Navegación -->
        <header class="navbar-ugb">
            <a href="#" @click.prevent="cambiarTab('inicio')" class="navbar-brand-ugb">
                <img src="{{ asset('images/LOGO_WEB_INF.png') }}" alt="Logo UGB" style="height: 80px; object-fit: contain; filter: brightness(0) invert(1);">
            </a>

            <div class="navbar-links">
                <a href="#" @click.prevent="cambiarTab('empresas')" class="nav-item-ugb" :class="{ 'active-tab': activeTab === 'empresas' }">Empresas socias</a>
                <a href="#" @click.prevent="cambiarTab('areas')" class="nav-item-ugb" :class="{ 'active-tab': activeTab === 'areas' }">Áreas de pasantías</a>
                <a href="#" @click.prevent="cambiarTab('requisitos')" class="nav-item-ugb" :class="{ 'active-tab': activeTab === 'requisitos' }">Requisitos</a>
                <a href="#" @click.prevent="cambiarTab('alcance')" class="nav-item-ugb" :class="{ 'active-tab': activeTab === 'alcance' }">Alcance</a>
            </div>

            <div class="nav-actions">
                <a href="/login?vista=registro" class="btn-register-ugb">Registrarse</a>
                <a href="/login" class="btn-login-ugb">Iniciar sesión</a>
            </div>
        </header>

        <!-- Contenido Principal Dinámico -->
        <main class="main-container">
            <transition name="fade" mode="out-in">
                
                <!-- ── VISTA INICIAL (INICIO) ── -->
                <div v-if="activeTab === 'inicio'" key="inicio">
                    <div class="hero-row">
                        <div class="hero-text-block">
                            <h2 class="hero-title">
                                Conectamos el talento técnico con las empresas líderes del sector tecnológico para transformar el aprendizaje en experiencia profesional real.
                            </h2>
                        </div>
                        <div class="hero-image-block">
                            <img src="{{ asset('images/Web_info_imagen_1.png') }}" alt="Mentoría tecnológica" class="hero-img">
                        </div>
                    </div>
                    <div class="hero-row reverse-mobile">
                        <div class="hero-image-block">
                            <img src="{{ asset('images/web_info_imagen_2.png') }}" alt="Equipo de desarrollo" class="hero-img">
                        </div>
                        <div class="hero-text-block">
                            <h2 class="hero-title">
                                Génesis Profesional es el puente entre tus estudios y tu primera experiencia en el mundo laboral. Encuentra la pasantía ideal y potencia tus habilidades.
                            </h2>
                        </div>
                    </div>
                </div>

                <!-- ── VISTA EMPRESAS SOCIAS ── -->
                <div v-else-if="activeTab === 'empresas'" key="empresas">
                    <!-- Fila 1 -->
                    <div class="asymmetric-block">
                        <div class="block-text">
                            <h2 class="hero-title">
                                Contamos con una red estratégica de aliados comprometidos con el desarrollo del talento joven. Nuestras empresas socias pertenecen a diversos sectores productivos.
                            </h2>
                        </div>
                        <div class="block-img-container">
                            <img src="{{ asset('images/empresas_socias_imagen_1.png') }}" alt="Alianza estratégica" class="block-img">
                        </div>
                    </div>
                    <!-- Fila 2 -->
                    <div class="asymmetric-block" style="flex-direction: row-reverse;">
                        <div class="block-text">
                            <h2 class="hero-title">
                                Brindamos espacios de aprendizaje real en áreas de tecnología. Cada convenio garantiza que el estudiante se integre a un entorno profesional de alto nivel.
                            </h2>
                        </div>
                        <div class="block-img-container">
                            <img src="{{ asset('images/empresas_socias_imagen_2.png') }}" alt="Espacio de trabajo" class="block-img">
                        </div>
                    </div>
                </div>

                <!-- ── VISTA ÁREAS DE PASANTÍAS ── -->
                <div v-else-if="activeTab === 'areas'" key="areas">
                    <div class="hero-row mb-5">
                        <div class="hero-text-block">
                            <h2 class="hero-title">
                                Contamos con vacantes especializadas para fortalecer el perfil técnico de nuestros futuros ingenieros en las siguientes dimensiones:
                            </h2>
                        </div>
                        <div class="hero-image-block">
                            <img src="{{ asset('images/areas_imagen_1.png') }}" alt="Ingenieros trabajando" class="hero-img">
                        </div>
                    </div>

                    <!-- Slider / Carrusel de Dimensiones -->
                    <div class="slider-wrapper my-5">
                        <button class="slider-btn" @click="prevAreaSlide" v-if="areaSlideIndex > 0">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        
                        <div class="slider-cards-row">
                            <div v-for="area in visibleAreas" :key="area.titulo" class="card-ds">
                                <img :src="area.img" class="card-ds-img" alt="Dimension">
                                <div class="card-ds-body">
                                    <strong>@{{ area.titulo }}:</strong> @{{ area.desc }}
                                </div>
                            </div>
                        </div>

                        <button class="slider-btn" @click="nextAreaSlide" v-if="areaSlideIndex === 0">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- ── VISTA REQUISITOS ── -->
                <div v-else-if="activeTab === 'requisitos'" key="requisitos">
                    <h3 class="hero-title mb-5 font-serif" style="font-size: 32px;">Requisitos para el proceso de pasantía:</h3>
                    
                    <!-- Fila 1 -->
                    <div class="asymmetric-block">
                        <div class="block-img-container">
                            <img src="{{ asset('images/requisitos_imagen_1.png') }}" alt="Estudiante en laptop" class="block-img">
                        </div>
                        <div class="block-text">
                            <ul class="requisito-list">
                                <li>
                                    <strong>Estado Académico:</strong> Ser estudiante activo de la carrera de Ingeniería en Sistemas y Redes Informáticas y haber aprobado al menos el 70% de las unidades valorativas del plan de estudios.
                                </li>
                                <li>
                                    <strong>Rendimiento:</strong> Poseer un Coeficiente de Unidades de Mérito (CUM) igual o mayor a 7.0.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="asymmetric-block" style="flex-direction: row-reverse;">
                        <div class="block-img-container">
                            <img src="{{ asset('images/requisitos_imagen_2.png') }}" alt="Reunión de asesoría" class="block-img">
                        </div>
                        <div class="block-text">
                            <ul class="requisito-list">
                                <li>
                                    <strong>Competencias Técnicas:</strong> Aprobar las evaluaciones previas para determinar si es aprobado o no para el proceso de pasantías en el área solicitada.
                                </li>
                                <li>
                                    <strong>Disponibilidad Horaria:</strong> Disponer del tiempo necesario para cumplir con la jornada acordada con la empresa, sin afectar el horario de las materias inscritas en el ciclo actual.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ── VISTA ALCANCE ── -->
                <div v-else-if="activeTab === 'alcance'" key="alcance">
                    <div class="hero-row mb-5">
                        <div class="hero-text-block">
                            <h2 class="hero-title">
                                El programa de pasantías tiene como objetivo la transición efectiva del aula al entorno laboral.
                            </h2>
                        </div>
                        <div class="hero-image-block">
                            <img src="{{ asset('images/alcances_imagen_1.png') }}" alt="Reunión académica" class="hero-img">
                        </div>
                    </div>

                    <h3 class="hero-title mb-4 font-serif" style="font-size: 28px;">El alcance comprende:</h3>

                    <div class="slider-wrapper my-4">
                        <button class="slider-btn" @click="prevAlcanceSlide" v-if="alcanceSlideIndex > 0">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        
                        <div class="slider-cards-row">
                            <div v-for="alcance in visibleAlcances" :key="alcance.titulo" class="card-ds">
                                <img :src="alcance.img" class="card-ds-img" alt="Alcance">
                                <div class="card-ds-body">
                                    <strong>@{{ alcance.titulo }}:</strong> @{{ alcance.desc }}
                                </div>
                            </div>
                        </div>

                        <button class="slider-btn" @click="nextAlcanceSlide" v-if="alcanceSlideIndex === 0">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

            </transition>
        </main>

        <footer class="footer-ugb">
            <div class="footer-inner" style="max-width: 1300px; margin: 0 auto;">
                <div class="row footer-grid align-items-center">
                    <!-- Columna Logo -->
                    <div class="col-md-5 mb-4 mb-md-0 d-flex align-items-center justify-content-center justify-content-md-start">
                        <img src="{{ asset('images/LOGO_WEB_INF.png') }}" alt="Logo UGB" style="height: 120px; object-fit: contain; filter: brightness(0) invert(1);">
                    </div>

                    <!-- Columna Redes Sociales -->
                    <div class="col-md-3 mb-4 mb-md-0 d-flex flex-column align-items-center align-items-md-start justify-content-center">
                        <h4 class="footer-social-title">Redes sociales</h4>
                        <div class="footer-social-icons">
                            <a href="https://www.instagram.com/ugb.sv?igsh=anBwMTl5MnlxanAz" target="_blank" rel="noopener noreferrer" class="footer-social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="https://www.facebook.com/share/1BLV1hfAJB/" target="_blank" rel="noopener noreferrer" class="footer-social-icon"><i class="bi bi-facebook"></i></a>
                        </div>
                    </div>

                    <!-- Columna Contacto -->
                    <div class="col-md-4 d-flex flex-column align-items-center align-items-md-start justify-content-center footer-contact-section">
                        <h4 class="footer-contact-title">Contacto:</h4>
                        <div class="footer-contact-item">
                            <i class="bi bi-telephone"></i>
                            <span>(503) 2645 6500</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="bi bi-envelope"></i>
                            <span>consultas@ugb.edu.sv</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Vue App Script -->
    <script>
        const { createApp, ref, computed } = Vue;

        createApp({
            setup() {
                const activeTab = ref('inicio');
                const areaSlideIndex = ref(0);
                const alcanceSlideIndex = ref(0);

                const areasPasantia = [
                    {
                        titulo: "Desarrollo de Software y Web",
                        desc: "Participación en el ciclo de vida de aplicaciones, manejo de frameworks (Frontend/Backend), gestión de bases de datos SQL/NoSQL y metodologías ágiles.",
                        img: "/images/desarollo_web.png"
                    },
                    {
                        titulo: "Infraestructura y Redes",
                        desc: "Configuración y mantenimiento de redes locales (LAN/WAN), administración de servidores, gestión de servicios en la nube (Cloud) y soporte a equipos de comunicación.",
                        img: "/images/redes_infraestructura.png"
                    },
                    {
                        titulo: "Aseguramiento de Calidad (QA) y Testing",
                        desc: "Ejecución de pruebas funcionales, no funcionales, garantiza la viabilidad del software, diseño de pruebas, detección de errores y uso de herramientas para la automatización de pruebas.",
                        img: "/images/seguramiento_QA.png"
                    },
                    {
                        titulo: "Seguridad Informática",
                        desc: "Implementación de protocolos de seguridad, análisis de vulnerabilidades, gestión de identidades y auditoría de sistemas para la protección de activos digitales.",
                        img: "/images/areas_seguridad_informatica.png"
                    }
                ];

                const alcancesList = [
                    {
                        titulo: "Evaluación",
                        desc: "Seguimiento continuo por parte de un tutor empresarial y un supervisor docente para validar el crecimiento de competencias técnicas y habilidades blandas.",
                        img: "/images/alcances_evaluacion.png"
                    },
                    {
                        titulo: "Formación Práctica",
                        desc: "Ejecución de tareas vinculadas directamente a la especialidad del estudiante.",
                        img: "/images/alcances_formacion_practica.png"
                    },
                    {
                        titulo: "Duración",
                        desc: "Cumplimiento de las horas sociales o profesionales estipuladas en el reglamento académico.",
                        img: "/images/alcances_duracion.png"
                    },
                    {
                        titulo: "Vinculación Profesional",
                        desc: "Creación de redes de contacto estratégicas dentro del sector tecnológico y posibilidad de transición a una plaza fija, basada en el desempeño demostrado.",
                        img: "/images/alcances_vinculacion_profesional.png"
                    }
                ];

                const visibleAreas = computed(() => {
                    if (areaSlideIndex.value === 0) {
                        return areasPasantia.slice(0, 3);
                    } else {
                        return areasPasantia.slice(1, 4);
                    }
                });

                const visibleAlcances = computed(() => {
                    if (alcanceSlideIndex.value === 0) {
                        return alcancesList.slice(0, 3);
                    } else {
                        return alcancesList.slice(1, 4);
                    }
                });

                const cambiarTab = (tab) => {
                    activeTab.value = tab;
                    areaSlideIndex.value = 0;
                    alcanceSlideIndex.value = 0;
                };

                const nextAreaSlide = () => {
                    areaSlideIndex.value = 1;
                };

                const prevAreaSlide = () => {
                    areaSlideIndex.value = 0;
                };

                const nextAlcanceSlide = () => {
                    alcanceSlideIndex.value = 1;
                };

                const prevAlcanceSlide = () => {
                    alcanceSlideIndex.value = 0;
                };

                return {
                    activeTab,
                    areaSlideIndex,
                    alcanceSlideIndex,
                    visibleAreas,
                    visibleAlcances,
                    cambiarTab,
                    nextAreaSlide,
                    prevAreaSlide,
                    nextAlcanceSlide,
                    prevAlcanceSlide
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
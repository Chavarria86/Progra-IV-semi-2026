<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Inscripciones (Vue)</title>
    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uuid/8.3.2/uuid.min.js"></script>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style> body { background-color: #f8f9fa; padding: 2rem 0; } </style>
</head>
<body>
    <div class="container mt-4 mb-2">
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
            &larr; Cerrar Formulario y Volver
        </a>
    </div>
    <div id="app" class="container"></div>

    <!-- AlertifyJS for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <script type="module">
        import inscripciones from '/js/inscripciones.js';

        const app = Vue.createApp({
            data() {
                return {
                    forms: {
                        inscripciones: { mostrar: true }
                    }
                }
            },
            template: `
                <div v-if="forms.inscripciones.mostrar">
                    <inscripciones-component :forms="forms"></inscripciones-component>
                </div>
            `
        });

        app.component('inscripciones-component', inscripciones);
        app.mount('#app');
    </script>
</body>
</html>
<?php /**PATH C:\Users\josec\OneDrive\Escritorio\Progra-IV-semi-2026\proyectolavarel1\resources\views/inscripciones/vue.blade.php ENDPATH**/ ?>
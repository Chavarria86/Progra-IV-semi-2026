<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Alumnos (Vue)</title>
    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <!-- UUID Library (Optional, Vue uses it in old code) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uuid/8.3.2/uuid.min.js"></script>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        body { background-color: #f8f9fa; padding: 2rem 0; }
    </style>
</head>
<body>
    <div class="container mt-4 mb-2">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            &larr; Cerrar Formulario y Volver al Dashboard
        </a>
    </div>
    <div id="app" class="container"></div>

    <!-- AlertifyJS for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <!-- Load the rewritten Vue Component stored in public -->
    <script type="module">
        import alumnos from '/js/alumnos.js';

        const app = Vue.createApp({
            data() {
                return {
                    forms: {
                        alumnos: { mostrar: true },
                        busqueda_alumnos: { mostrar: false }
                    }
                }
            },
            template: `
                <div v-if="forms.alumnos.mostrar">
                    <alumnos-component :forms="forms"></alumnos-component>
                </div>
            `
        });

        // Register custom directive explicitly inside Vue 3
        app.directive('draggable', {
            mounted(el) {
                // simple draggability bypass for testing
                el.style.position = 'relative';
            }
        });

        // Register the local component
        app.component('alumnos-component', alumnos);
        app.mount('#app');
    </script>
</body>
</html>

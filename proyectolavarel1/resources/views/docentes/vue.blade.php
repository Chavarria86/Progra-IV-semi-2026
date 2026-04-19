<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Docentes (Vue)</title>
    <!-- Vue 3 CDN & Bootstrap -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uuid/8.3.2/uuid.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style> body { background-color: #f8f9fa; padding: 2rem 0; } </style>
</head>
<body>
    <div class="container mt-4 mb-2">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            &larr; Cerrar Formulario y Volver
        </a>
    </div>
    <div id="app" class="container"></div>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <script type="module">
        import docentes from '/js/docentes.js';

        const app = Vue.createApp({
            template: `<docentes-component></docentes-component>`
        });
        
        app.component('docentes-component', docentes);
        app.mount('#app');
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génesis Profesional - Dashboards</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- AlertifyJS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script>
        // Configuración global de AlertifyJS para los Dashboards
        document.addEventListener('DOMContentLoaded', function() {
            alertify.set('notifier','position', 'top-right');
            alertify.defaults.theme.ok = "btn btn-alertify-ok";
            alertify.defaults.theme.cancel = "btn btn-alertify-cancel";
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: #F4F4F4;">
    <div id="app"></div>
</body>
</html>

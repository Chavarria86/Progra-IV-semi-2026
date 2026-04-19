<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>::. Sistema Académico Liceo Británico ..::</title>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/bootstrap.min.css"/>
    </head>
    <body class="antialiased">
        <div id="app">
            <nav class="navbar navbar-expand-lg bg-light shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">SISTEMA ACADÉMICO</a>
                    <div class="collapse navbar-collapse">
                        <div class="navbar-nav">
                            <a class="nav-link" href="{{ route('estudiantes.index') }}">Alumnos</a>
                            <a class="nav-link" href="{{ route('docentes.index') }}">Docentes</a>
                            <a class="nav-link" href="{{ route('materias.index') }}">Materias</a>
                            <a class="nav-link" href="{{ route('matriculas.index') }}">Matrículas</a>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="py-4">
                @yield('content') </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @stack('scripts') </body>
</html>
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

if (DB::getDriverName() === 'pgsql') {
    DB::statement('TRUNCATE TABLE postulaciones, informes, solicitudes_supervisor, vacantes, curriculum_vitae CASCADE;');
} else {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Limpiar todas las tablas transaccionales
    DB::table('postulaciones')->truncate();
    DB::table('informes')->truncate();
    DB::table('solicitudes_supervisor')->truncate();
    DB::table('vacantes')->truncate();
    DB::table('curriculum_vitae')->truncate();
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}

// Resetear datos en la tabla pasantes para volver a 0, pero manteniendo el pasante en sí
DB::table('pasantes')->update([
    'supervisor_id' => null,
    'horas_aprobadas' => 0,
    'fase_actual' => 'Fase 1',
    'estado' => 'activo'
]);

echo "Limpieza de base de datos completada exitosamente. Solo se conservaron los usuarios y roles.\n";

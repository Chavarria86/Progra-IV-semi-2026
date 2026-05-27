<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTICO DE BASE DE DATOS ===\n\n";

echo "--- USUARIOS ---\n";
foreach(DB::table('usuarios')->get() as $u) {
    echo "  ID={$u->id} | {$u->nombres} {$u->apellidos} | rol={$u->rol} | correo={$u->correo_institucional}\n";
}

echo "\n--- PERSONAL ADMINISTRATIVO ---\n";
foreach(DB::table('personal_administrativo')->get() as $p) {
    echo "  ID={$p->id} | {$p->nombres} | cargo={$p->cargo} | correo={$p->correo_institucional}\n";
}

echo "\n--- PASANTES (con supervisor_id) ---\n";
foreach(DB::table('pasantes')->get() as $p) {
    echo "  pasante_id={$p->id} | usuario_id={$p->usuario_id} | supervisor_id=" . ($p->supervisor_id ?? 'NULL') . " | fase={$p->fase_actual} | horas={$p->horas_aprobadas}\n";
}

echo "\n--- INFORMES ---\n";
$informes = DB::table('informes')->get();
if($informes->isEmpty()) {
    echo "  [SIN INFORMES EN LA BASE DE DATOS]\n";
    echo "  El pasante aun no ha enviado ningun informe.\n";
} else {
    foreach($informes as $i) {
        echo "  informe_id={$i->id} | pasante_id={$i->pasante_id} | tipo={$i->tipo} | horas={$i->horas} | estado={$i->estado}\n";
    }
}

echo "\n--- SIMULANDO getInformesPendientes para supervisor logueado ---\n";
// Simular: supervisor@ugb.edu.sv inicia sesion -> usuarios.id = ?
$supUsuario = DB::table('usuarios')->where('correo_institucional', 'supervisor@ugb.edu.sv')->first();
if ($supUsuario) {
    echo "  supervisor en tabla usuarios: ID={$supUsuario->id}\n";
    $supAdmin = DB::table('personal_administrativo')->where('correo_institucional', 'supervisor@ugb.edu.sv')->first();
    if ($supAdmin) {
        echo "  supervisor en tabla personal_administrativo: ID={$supAdmin->id}\n";
        $pasantesIds = DB::table('pasantes')->where('supervisor_id', $supAdmin->id)->pluck('id');
        echo "  pasantes asignados a este supervisor: " . implode(', ', $pasantesIds->toArray() ?: ['ninguno']) . "\n";
        if ($pasantesIds->isNotEmpty()) {
            $informesPendientes = DB::table('informes')
                ->whereIn('pasante_id', $pasantesIds)
                ->where('estado', 'en_espera')
                ->get();
            echo "  informes en_espera de esos pasantes: " . $informesPendientes->count() . "\n";
        }
    } else {
        echo "  ERROR: No se encontro supervisor en personal_administrativo con ese correo!\n";
    }
} else {
    echo "  El supervisor no existe en tabla usuarios!\n";
}

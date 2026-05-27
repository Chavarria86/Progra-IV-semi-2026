<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Limpiar los estudiantes viejos creados anteriormente para mantener la BD limpia
$viejosCodigos = ['USM010126', 'USM020226', 'USSS030326', 'USSS040426', 'USM050526'];
DB::table('estudiantes')->whereIn('codigo_estudiante', $viejosCodigos)->delete();
echo "Estudiantes anteriores eliminados de la base de datos.\n";

$estudiantes = [
    [
        'codigo_estudiante' => 'USSS010120',
        'nombres' => 'Juan',
        'apellidos' => 'Pérez',
        'genero' => 'Masculino',
        'estado_civil' => 'Soltero',
        'dui' => '06012345-1',
        'direccion' => 'Colonia Médica, San Salvador',
        'fecha_nacimiento' => '2004-03-12',
        'departamento_nacimiento' => 'SAN SALVADOR',
        'municipio_nacimiento' => 'SAN SALVADOR',
        'pais' => 'EL SALVADOR',
        'correo_principal' => 'juan.perez@gmail.com',
        'correo_secundario' => 'usss010120@ugb.edu.sv',
        'telefono' => '2222-3333',
        'celular' => '7777-8888',
        'es_estudiante_activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'codigo_estudiante' => 'USSS020221',
        'nombres' => 'María',
        'apellidos' => 'Gómez',
        'genero' => 'Femenino',
        'estado_civil' => 'Soltera',
        'dui' => '06023456-2',
        'direccion' => 'Residencial Altavista, Ilopango',
        'fecha_nacimiento' => '2003-07-22',
        'departamento_nacimiento' => 'SAN SALVADOR',
        'municipio_nacimiento' => 'ILOPANGO',
        'pais' => 'EL SALVADOR',
        'correo_principal' => 'maria.gomez@gmail.com',
        'correo_secundario' => 'usss020221@ugb.edu.sv',
        'telefono' => '2233-4455',
        'celular' => '7654-3210',
        'es_estudiante_activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'codigo_estudiante' => 'USSS030322',
        'nombres' => 'Carlos',
        'apellidos' => 'López',
        'genero' => 'Masculino',
        'estado_civil' => 'Soltero',
        'dui' => '06034567-3',
        'direccion' => 'Urbanización La Cima, San Salvador',
        'fecha_nacimiento' => '2004-11-05',
        'departamento_nacimiento' => 'SAN SALVADOR',
        'municipio_nacimiento' => 'SAN SALVADOR',
        'pais' => 'EL SALVADOR',
        'correo_principal' => 'carlos.lopez@gmail.com',
        'correo_secundario' => 'usss030322@ugb.edu.sv',
        'telefono' => '2244-5566',
        'celular' => '7890-1234',
        'es_estudiante_activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'codigo_estudiante' => 'USSS040423',
        'nombres' => 'Gabriela',
        'apellidos' => 'Fernández',
        'genero' => 'Femenino',
        'estado_civil' => 'Soltera',
        'dui' => '06045678-4',
        'direccion' => 'Colonia Escalón, San Salvador',
        'fecha_nacimiento' => '2005-01-30',
        'departamento_nacimiento' => 'SAN SALVADOR',
        'municipio_nacimiento' => 'SAN SALVADOR',
        'pais' => 'EL SALVADOR',
        'correo_principal' => 'gaby.fer@gmail.com',
        'correo_secundario' => 'usss040423@ugb.edu.sv',
        'telefono' => '2255-6677',
        'celular' => '7531-9753',
        'es_estudiante_activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'codigo_estudiante' => 'USSS050524',
        'nombres' => 'Luis',
        'apellidos' => 'Rodríguez',
        'genero' => 'Masculino',
        'estado_civil' => 'Soltero',
        'dui' => '06056789-5',
        'direccion' => 'Barrio El Calvario, San Miguel',
        'fecha_nacimiento' => '2004-09-18',
        'departamento_nacimiento' => 'SAN MIGUEL',
        'municipio_nacimiento' => 'SAN MIGUEL',
        'pais' => 'EL SALVADOR',
        'correo_principal' => 'luis.rod@gmail.com',
        'correo_secundario' => 'usss050524@ugb.edu.sv',
        'telefono' => '2661-2222',
        'celular' => '7182-9304',
        'es_estudiante_activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]
];

foreach ($estudiantes as $estudiante) {
    $existe = DB::table('estudiantes')
        ->where('codigo_estudiante', $estudiante['codigo_estudiante'])
        ->orWhere('correo_secundario', $estudiante['correo_secundario'])
        ->exists();

    if (!$existe) {
        DB::table('estudiantes')->insert($estudiante);
        echo "Estudiante {$estudiante['nombres']} {$estudiante['apellidos']} ({$estudiante['codigo_estudiante']}) insertado con éxito.\n";
    } else {
        echo "Estudiante {$estudiante['nombres']} {$estudiante['apellidos']} ({$estudiante['codigo_estudiante']}) ya existe.\n";
    }
}
echo "Proceso completado.\n";

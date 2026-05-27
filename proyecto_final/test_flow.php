<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Pasante;
use App\Models\PersonalAdministrativo;
use App\Models\Vacante;
use App\Models\Postulacion;
use App\Models\Informe;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

echo "=== INICIANDO PRUEBA DE FLUJO COMPLETO ===\n\n";

// 1. Limpiar datos de prueba anteriores
echo "[1] Limpiando datos de prueba anteriores...\n";
DB::table('informes')->where('tipo', 'parcial')->delete();
DB::table('postulaciones')->delete();
DB::table('vacantes')->delete();
DB::table('solicitudes_supervisor')->delete();
DB::table('curriculum_vitae')->delete();

// 2. Obtener usuarios de prueba
$pasanteUser = Usuario::where('rol', 'pasante')->first();
$supervisorUser = PersonalAdministrativo::first();

if (!$pasanteUser || !$supervisorUser) {
    die("Error: No se encontraron usuarios de prueba (pasante y supervisor).\n");
}

$pasante = Pasante::where('usuario_id', $pasanteUser->id)->first();
if (!$pasante) {
    // Si no hay pasante, lo creamos
    $pasante = Pasante::create([
        'usuario_id' => $pasanteUser->id,
        'carrera' => 'Ingeniería en Sistemas',
        'area' => 'desarrollo',
        'fase_actual' => 'fase1',
        'horas_aprobadas' => 0
    ]);
}
// Resetear horas y supervisor
$pasante->update(['horas_aprobadas' => 0, 'supervisor_id' => null, 'fase_actual' => 'fase1']);

echo "Pasante seleccionado: " . $pasanteUser->nombres . "\n";
echo "Supervisor seleccionado: " . $supervisorUser->nombres . "\n\n";

// 3. Pasante solicita supervisor
echo "[2] Pasante solicitando supervisor...\n";
$controller = app()->make(\App\Http\Controllers\PasanteController::class);
$request = Request::create('/api/pasante/solicitar-supervisor', 'POST');
$request->headers->set('X-User-Id', $pasanteUser->id);
$response = $controller->solicitarSupervisor($request);
echo "Respuesta: " . $response->getContent() . "\n";

$solicitud = DB::table('solicitudes_supervisor')->where('pasante_id', $pasante->id)->first();
echo "Solicitud creada ID: " . $solicitud->id . " | Estado: " . $solicitud->estado . "\n\n";

// 4. Supervisor acepta la solicitud
echo "[3] Supervisor aceptando solicitud...\n";
$supController = app()->make(\App\Http\Controllers\SupervisorController::class);
$request = Request::create("/api/supervisor/solicitudes/{$solicitud->id}/responder", 'PUT', ['decision' => 'aceptar']);
$request->headers->set('X-User-Id', $supervisorUser->id);
$response = $supController->responderSolicitud($request, $solicitud->id);
echo "Respuesta: " . $response->getContent() . "\n";

$pasante->refresh();
echo "Supervisor asignado al pasante: " . ($pasante->supervisor_id == $supervisorUser->id ? "SI ($supervisorUser->id)" : "NO") . "\n\n";

// 5. Vicedecano crea una vacante
echo "[4] Vicedecano creando vacante...\n";
$vdController = app()->make(\App\Http\Controllers\ViceDecanoController::class);
$request = Request::create('/api/vicedecano/vacantes', 'POST', [
    'empresa' => 'AgenteTech Corp',
    'area' => 'desarrollo',
    'descripcion' => 'Desarrollador backend para proyecto de IA',
    'estado' => 'activa'
]);
$response = $vdController->crearVacante($request);
echo "Respuesta: " . $response->getContent() . "\n";
$vacanteData = json_decode($response->getContent());
$vacanteId = $vacanteData->vacante->id;
echo "Vacante creada ID: " . $vacanteId . "\n\n";

// 6. Pasante aplica a la vacante
echo "[5] Pasante aplicando a vacante...\n";
// Insertamos un CV mock
$cvId = DB::table('curriculum_vitae')->insertGetId([
    'usuario_id' => $pasanteUser->id,
    'titulo_cv' => 'CV Backend',
    'nombre_archivo' => 'cv.pdf',
    'ruta_archivo' => '/storage/cv.pdf',
    'estado' => 'activo',
    'created_at' => now(),
    'updated_at' => now()
]);

$request = Request::create("/api/pasante/vacantes/{$vacanteId}/aplicar", 'POST', ['cv_id' => $cvId]);
$request->headers->set('X-User-Id', $pasanteUser->id);
$response = $controller->aplicarVacante($request, $vacanteId);
echo "Respuesta: " . $response->getContent() . "\n";
$postulacion = DB::table('postulaciones')->where('pasante_id', $pasante->id)->first();
echo "Postulacion estado: " . $postulacion->estado . "\n\n";

// 7. Supervisor aprueba postulación
echo "[6] Supervisor aprobando postulación...\n";
$request = Request::create("/api/supervisor/postulaciones/{$postulacion->id}/responder", 'PUT', ['decision' => 'aceptar']);
$request->headers->set('X-User-Id', $supervisorUser->id);
$response = $supController->responderPostulacion($request, $postulacion->id);
echo "Respuesta: " . $response->getContent() . "\n";
$postulacion = DB::table('postulaciones')->where('id', $postulacion->id)->first();
echo "Postulacion estado final: " . $postulacion->estado . "\n\n";

// 8. Pasante sube informe mensual
echo "[7] Pasante subiendo informe parcial (150 horas)...\n";
$informeId = DB::table('informes')->insertGetId([
    'pasante_id' => $pasante->id,
    'tipo' => 'parcial',
    'horas' => 150,
    'archivo_url' => '/storage/informe.pdf',
    'estado' => 'en_espera'
]);
$informe = DB::table('informes')->where('id', $informeId)->first();
echo "Informe creado ID: " . $informe->id . " | Horas: " . $informe->horas . "\n\n";

// 9. Supervisor aprueba informe y se suman las horas
echo "[8] Supervisor aprobando informe...\n";
$request = Request::create("/api/supervisor/informes/{$informe->id}/evaluar", 'PUT', ['decision' => 'aprobar']);
$request->headers->set('X-User-Id', $supervisorUser->id);
$response = $supController->evaluarInforme($request, $informe->id);
echo "Respuesta: " . $response->getContent() . "\n";

$pasante->refresh();
echo "================ RESULTADOS FINALES ================\n";
echo "Horas aprobadas del pasante: " . $pasante->horas_aprobadas . "\n";
echo "Fase actual del pasante: " . $pasante->fase_actual . "\n";
if ($pasante->horas_aprobadas == 150 && $pasante->fase_actual == 'Fase 2') {
    echo "¡PRUEBA EXITOSA! El sistema funciona y guardó todo en la base de datos.\n";
} else {
    echo "¡HUBO UN ERROR! Las horas o la fase no se actualizaron como se esperaba.\n";
}

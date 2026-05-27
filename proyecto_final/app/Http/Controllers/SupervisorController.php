<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Pasante;
use App\Models\CurriculumVitae;
use App\Models\Postulacion;
use App\Models\Informe;
use App\Models\Vacante;
use App\Models\PersonalAdministrativo;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    /**
     * Resuelve el ID correcto en personal_administrativo a partir
     * del ID del usuario autenticado (que viene de la tabla usuarios).
     * Esto es necesario porque pasantes.supervisor_id apunta a
     * personal_administrativo.id, no a usuarios.id.
     */
    private function resolverSupervisorId($usuarioId)
    {
        // Primero intentamos encontrar al usuario para obtener su correo
        $usuario = Usuario::find($usuarioId);
        if ($usuario) {
            $admin = PersonalAdministrativo::where('correo_institucional', $usuario->correo_institucional)->first();
            if ($admin) return $admin->id;
        }
        // Fallback: buscar directamente como personal_administrativo.id
        $admin = PersonalAdministrativo::find($usuarioId);
        if ($admin) return $admin->id;
        // Último fallback: primer supervisor del sistema
        $primerSupervisor = PersonalAdministrativo::where('cargo', 'supervisor')->first();
        return $primerSupervisor ? $primerSupervisor->id : 1;
    }

    // Obtener los pasantes asignados a este supervisor
    public function getPasantes(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));

        $pasantes = Pasante::with('usuario')
            ->where('supervisor_id', $supervisor_id)
            ->get()
            ->map(function ($p) {
                return (object)[
                    'nombre' => $p->usuario->nombres ?? '',
                    'apellido' => $p->usuario->apellidos ?? '',
                    'correo' => $p->usuario->correo_institucional ?? '',
                    'pasante_id' => $p->id,
                    'area' => $p->area,
                    'estado' => $p->estado,
                    'fase_actual' => $p->fase_actual
                ];
            });

        return response()->json(['pasantes' => $pasantes]);
    }

    // Validar el CV de un pasante
    public function validarCV(Request $request, $id)
    {
        $cv = CurriculumVitae::find($id);
        if (!$cv) {
            return response()->json(['mensaje' => 'CV no encontrado.'], 404);
        }

        $cv->update(['estado' => 'validado']);

        $pasante = Pasante::where('usuario_id', $cv->usuario_id)->first();
        if ($pasante) {
            $pasante->update(['fase_actual' => 'F2']);
        }

        return response()->json(['mensaje' => 'CV validado exitosamente. Pasante avanza a Fase 2.']);
    }

    // Rechazar el CV de un pasante
    public function rechazarCV(Request $request, $id)
    {
        $request->validate(['observaciones' => 'required|string']);

        $cv = CurriculumVitae::find($id);
        if (!$cv) {
            return response()->json(['mensaje' => 'CV no encontrado.'], 404);
        }

        $cv->update([
            'estado' => 'rechazado', 
            'observaciones' => $request->observaciones
        ]);

        return response()->json(['mensaje' => 'CV rechazado. Se han guardado las observaciones.']);
    }

    // Asignar un pasante a una vacante o empresa
    public function asignarPasante(Request $request)
    {
        $request->validate([
            'pasante_id' => 'required|integer',
            'vacante_id' => 'required|integer'
        ]);

        $pasante_id = $request->pasante_id;
        $vacante_id = $request->vacante_id;

        $postulacion = Postulacion::where('pasante_id', $pasante_id)
            ->where('vacante_id', $vacante_id)
            ->first();

        if ($postulacion) {
            $postulacion->update(['estado' => 'aceptada']);
        } else {
            Postulacion::create([
                'pasante_id' => $pasante_id,
                'vacante_id' => $vacante_id,
                'estado' => 'aceptada'
            ]);
        }

        $pasante = Pasante::find($pasante_id);
        if ($pasante) {
            $pasante->update(['fase_actual' => 'F3']);
        }

        return response()->json(['mensaje' => 'Pasante asignado exitosamente. Avanza a Fase 3.']);
    }

    // Obtener CVs pendientes de validación
    public function getCvsPendientes(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));

        // Obtenemos los pasantes de este supervisor
        $pasantesIds = Pasante::where('supervisor_id', $supervisor_id)->pluck('usuario_id');

        $cvs = CurriculumVitae::whereIn('usuario_id', $pasantesIds)
            ->where('estado', 'activo') // O 'subido' o 'pendiente' dependiendo cómo lo guardemos
            ->with('usuario')
            ->get()
            ->map(function ($cv) {
                return (object)[
                    'id' => $cv->id,
                    'nombre_pasante' => ($cv->usuario->nombres ?? '') . ' ' . ($cv->usuario->apellidos ?? ''),
                    'titulo' => $cv->titulo_cv ?? 'Mi CV',
                    'fecha_subida' => $cv->created_at->format('d M Y'),
                    'estado' => $cv->estado,
                    'url_publica' => $cv->url_publica
                ];
            });

        return response()->json(['cvs' => $cvs]);
    }

    // Obtener informes pendientes
    public function getInformesPendientes(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));
        $pasantesIds = Pasante::where('supervisor_id', $supervisor_id)->pluck('id');

        $informes = Informe::with('pasante.usuario')
            ->whereIn('pasante_id', $pasantesIds)
            ->where('estado', 'en_espera')
            ->get()
            ->map(function ($inf) {
                return (object)[
                    'id' => $inf->id,
                    'pasante' => ($inf->pasante->usuario->nombres ?? '') . ' ' . ($inf->pasante->usuario->apellidos ?? ''),
                    'nombre' => $inf->nombre ?: ('Informe ' . ucfirst($inf->tipo)),
                    'fecha' => $inf->created_at ? $inf->created_at->format('d M Y') : date('d M Y'),
                    'horas' => $inf->horas ?? 0,
                    'objetivos' => $inf->objetivos ?: 'No se registraron objetivos en el formulario.',
                    'actividades' => $inf->actividades ?: 'No se registraron actividades en el formulario.',
                    'conclusiones' => $inf->conclusiones ?: 'No se registraron conclusiones en el formulario.',
                    'archivo_url' => $inf->archivo_url
                ];
            });

        return response()->json(['informes' => $informes]);
    }

    // Evaluar informe (aprobar / solicitar correcciones)
    public function evaluarInforme(Request $request, $id)
    {
        $request->validate(['decision' => 'required|in:aprobar,rechazar']);
        
        $informe = Informe::with('pasante')->find($id);
        if (!$informe) return response()->json(['mensaje' => 'Informe no encontrado'], 404);

        if ($request->decision === 'aprobar') {
            $informe->update(['estado' => 'aprobado']);
            
            // Actualizar el progreso del pasante
            if ($informe->pasante) {
                $pasante = $informe->pasante;
                $nuevasHoras = $pasante->horas_aprobadas + $informe->horas;
                
                // Lógica simple de avance de fase (ej: cada 100 horas avanza)
                $nuevaFase = 'Fase 1';
                if ($nuevasHoras >= 100) $nuevaFase = 'Fase 2';
                if ($nuevasHoras >= 200) $nuevaFase = 'Fase 3';
                if ($nuevasHoras >= 300) $nuevaFase = 'Fase Final';

                $pasante->update([
                    'horas_aprobadas' => $nuevasHoras,
                    'fase_actual' => $nuevaFase
                ]);
            }

            return response()->json(['mensaje' => 'Informe aprobado correctamente. Las horas se han sumado al historial del pasante.']);
        } else {
            $informe->update(['estado' => 'correccion']);
            return response()->json(['mensaje' => 'Informe rechazado para correcciones.']);
        }
    }

    // Obtener postulaciones a vacantes de sus pasantes a cargo
    public function getPostulaciones(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));
        
        $pasantesIds = Pasante::where('supervisor_id', $supervisor_id)->pluck('id');

        $solicitudes = Postulacion::with(['pasante.usuario', 'vacante'])
            ->whereIn('pasante_id', $pasantesIds)
            ->where('estado', 'pendiente')
            ->get()
            ->map(function ($post) {
                return (object)[
                    'id' => $post->id,
                    'pasante' => ($post->pasante->usuario->nombres ?? '') . ' ' . ($post->pasante->usuario->apellidos ?? ''),
                    'vacante' => $post->vacante->empresa . ' - ' . $post->vacante->area,
                    'estado' => $post->estado,
                    'cv_url' => $post->cv ? $post->cv->url_publica : null,
                    'fecha' => $post->created_at->format('d M Y')
                ];
            });

        return response()->json(['postulaciones' => $solicitudes]);
    }

    // Responder a postulación (aprobar_por_supervisor)
    public function responderPostulacion(Request $request, $id)
    {
        $request->validate(['decision' => 'required|in:aceptar,rechazar']);
        
        $postulacion = Postulacion::find($id);
        if (!$postulacion) return response()->json(['mensaje' => 'Solicitud no encontrada'], 404);

        if ($request->decision === 'aceptar') {
            $postulacion->update(['estado' => 'aprobado_por_supervisor']);
            return response()->json(['mensaje' => 'Postulación aprobada. Será enviada al Vicedecano.']);
        } else {
            $postulacion->update(['estado' => 'rechazada']);
            return response()->json(['mensaje' => 'Postulación rechazada.']);
        }
    }

    // Obtener los pasantes a cargo
    public function getMisPasantes(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));
        $pasantes = Pasante::with('usuario')->where('supervisor_id', $supervisor_id)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->usuario->nombres . ' ' . $p->usuario->apellidos,
                'correo' => $p->usuario->correo_institucional,
                'area' => $p->area,
                'fase_actual' => $p->fase_actual,
                'horas_aprobadas' => $p->horas_aprobadas
            ];
        });
        return response()->json(['pasantes' => $pasantes]);
    }

    // Crear Vacantes (Supervisor)
    public function crearVacante(Request $request)
    {
        $request->validate([
            'empresa' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'descripcion' => 'required|string'
        ]);

        $supervisor_id = $request->header('X-User-Id', 2);

        $vacante = Vacante::create([
            'empresa' => $request->empresa,
            'area' => $request->area,
            'descripcion' => $request->descripcion,
            'creador_id' => $supervisor_id,
            'estado' => 'activa'
        ]);

        return response()->json(['mensaje' => 'Vacante creada exitosamente.', 'vacante' => $vacante]);
    }

    // Obtener solicitudes de asignación de pasantes
    public function getSolicitudes(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));

        $solicitudes = DB::table('solicitudes_supervisor')
            ->join('pasantes', 'solicitudes_supervisor.pasante_id', '=', 'pasantes.id')
            ->join('usuarios', 'pasantes.usuario_id', '=', 'usuarios.id')
            ->where('solicitudes_supervisor.supervisor_id', $supervisor_id)
            ->select(
                'solicitudes_supervisor.id',
                'solicitudes_supervisor.mensaje',
                'solicitudes_supervisor.estado',
                'solicitudes_supervisor.created_at',
                'solicitudes_supervisor.updated_at',
                'usuarios.nombres',
                'usuarios.apellidos',
                'usuarios.correo_institucional',
                'pasantes.area'
            )
            ->orderByDesc('solicitudes_supervisor.created_at')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nombre' => $s->nombres,
                    'apellido' => $s->apellidos,
                    'correo' => $s->correo_institucional,
                    'carrera' => 'Ing. Sistemas',
                    'area' => $s->area,
                    'mensaje' => $s->mensaje,
                    'fecha' => \Carbon\Carbon::parse($s->created_at)->format('d M Y'),
                    'estado' => $s->estado
                ];
            });

        return response()->json(['solicitudes' => $solicitudes]);
    }

    // Responder solicitud de asignación
    public function responderSolicitud(Request $request, $id)
    {
        $request->validate(['decision' => 'required|in:aceptar,rechazar']);

        $usuario_id = $request->header('X-User-Id', 1);
        $supervisor_id = $this->resolverSupervisorId($usuario_id);
        $supervisor = PersonalAdministrativo::find($supervisor_id);
        if (!$supervisor) return response()->json(['mensaje' => 'Supervisor no encontrado'], 404);

        $solicitud = DB::table('solicitudes_supervisor')->where('id', $id)->first();
        if (!$solicitud) return response()->json(['mensaje' => 'Solicitud no encontrada'], 404);

        if ($request->decision === 'aceptar') {
            DB::table('solicitudes_supervisor')->where('id', $id)->update([
                'estado' => 'aceptada',
                'updated_at' => now()
            ]);
            // Asignar al supervisor actual
            Pasante::where('id', $solicitud->pasante_id)->update([
                'supervisor_id' => $supervisor_id
            ]);
            return response()->json(['mensaje' => 'Solicitud aceptada y pasante asignado.']);
        } else {
            DB::table('solicitudes_supervisor')->where('id', $id)->update([
                'estado' => 'rechazada',
                'updated_at' => now()
            ]);
            return response()->json(['mensaje' => 'Solicitud rechazada.']);
        }
    }

    // Obtener historial de recomendaciones
    public function getRecomendaciones(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));

        $recomendaciones = DB::table('recomendaciones')
            ->join('pasantes', 'recomendaciones.pasante_id', '=', 'pasantes.id')
            ->join('usuarios', 'pasantes.usuario_id', '=', 'usuarios.id')
            ->where('recomendaciones.supervisor_id', $supervisor_id)
            ->select(
                'recomendaciones.id',
                'recomendaciones.tipo',
                'recomendaciones.titulo',
                'recomendaciones.contenido',
                'recomendaciones.created_at',
                'usuarios.nombres',
                'usuarios.apellidos'
            )
            ->orderByDesc('recomendaciones.created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'pasante' => $r->nombres . ' ' . $r->apellidos,
                    'titulo' => $r->titulo,
                    'tipo' => $r->tipo,
                    'contenido' => $r->contenido,
                    'fecha' => \Carbon\Carbon::parse($r->created_at)->format('d M Y')
                ];
            });

        return response()->json(['recomendaciones' => $recomendaciones]);
    }

    // Crear carta de recomendacion
    public function crearRecomendacion(Request $request)
    {
        $request->validate([
            'pasante_id' => 'required|integer',
            'tipo' => 'required|string',
            'titulo' => 'required|string',
            'contenido' => 'required|string'
        ]);

        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));

        DB::table('recomendaciones')->insert([
            'pasante_id' => $request->pasante_id,
            'supervisor_id' => $supervisor_id,
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Recomendación guardada con éxito.']);
    }

    // Obtener estadísticas y actividades reales del supervisor
    public function getDashboard(Request $request)
    {
        $supervisor_id = $this->resolverSupervisorId($request->header('X-User-Id', 1));
        
        // Pasantes de este supervisor
        $pasantesQuery = Pasante::where('supervisor_id', $supervisor_id);
        $pasantesIds = $pasantesQuery->pluck('id');
        $usuarioIds = $pasantesQuery->pluck('usuario_id');

        $pasantesPendientes = Pasante::where('supervisor_id', $supervisor_id)->where('estado', 'en_proceso')->count();
        
        $cvsAprobados = CurriculumVitae::whereIn('usuario_id', $usuarioIds)->where('estado', 'validado')->count();
        
        $vacantesActivas = Vacante::where('estado', 'activa')->count();

        // Obtener actividad reciente real
        $actividad = [];

        // 1. Informes recientes
        $informesRecientes = Informe::with('pasante.usuario')
            ->whereIn('pasante_id', $pasantesIds)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
            
        foreach ($informesRecientes as $inf) {
            $estadoStr = '';
            if ($inf->estado === 'aprobado') {
                $estadoStr = 'Informe aprobado';
            } elseif ($inf->estado === 'correccion') {
                $estadoStr = 'Correcciones requeridas en informe';
            } else {
                $estadoStr = 'Informe entregado (En revisión)';
            }
            
            $actividad[] = [
                'id' => 'inf_' . $inf->id,
                'nombre' => ($inf->pasante->usuario->nombres ?? '') . ' ' . ($inf->pasante->usuario->apellidos ?? ''),
                'accion' => $estadoStr . ' de ' . $inf->horas . ' horas',
                'tipo' => $inf->estado === 'aprobado' ? 'aprobado' : 'pendiente',
                'fecha' => $inf->created_at ? $inf->created_at->diffForHumans() : 'Hace poco',
                'timestamp' => $inf->created_at ? $inf->created_at->timestamp : 0
            ];
        }

        // 2. CVs recientes
        $cvsRecientes = CurriculumVitae::with('usuario')
            ->whereIn('usuario_id', $usuarioIds)
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($cvsRecientes as $cv) {
            $estadoStr = '';
            if ($cv->estado === 'validado') {
                $estadoStr = 'CV aprobado — Avanza a Fase 2';
            } elseif ($cv->estado === 'rechazado') {
                $estadoStr = 'CV requiere correcciones';
            } else {
                $estadoStr = 'CV subido (En revisión)';
            }

            $actividad[] = [
                'id' => 'cv_' . $cv->id,
                'nombre' => ($cv->usuario->nombres ?? '') . ' ' . ($cv->usuario->apellidos ?? ''),
                'accion' => $estadoStr,
                'tipo' => $cv->estado === 'validado' ? 'aprobado' : 'pendiente',
                'fecha' => $cv->updated_at ? $cv->updated_at->diffForHumans() : 'Hace poco',
                'timestamp' => $cv->updated_at ? $cv->updated_at->timestamp : 0
            ];
        }

        // Ordenar actividad por timestamp descendente
        usort($actividad, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Tomar las 5 actividades más recientes
        $actividad = array_slice($actividad, 0, 5);

        return response()->json([
            'stats' => [
                'pasantesPendientes' => $pasantesPendientes,
                'cvsAprobados' => $cvsAprobados,
                'vacantesActivas' => $vacantesActivas
            ],
            'actividadReciente' => $actividad
        ]);
    }
}

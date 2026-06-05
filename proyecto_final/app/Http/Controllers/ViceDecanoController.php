<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pasante;
use App\Models\PersonalAdministrativo;
use App\Models\Informe;
use App\Models\Postulacion;
use App\Models\CurriculumVitae;

class ViceDecanoController extends Controller
{
    // Obtener estadísticas y métricas
    public function getDashboard()
    {
        $totalPasantes = Pasante::count();
        $totalSupervisores = PersonalAdministrativo::where('cargo', 'NOT LIKE', '%decano%')->count();
        $informesFinalesPendientes = Informe::where('tipo', 'final')->where('estado', 'en_espera')->count();
        $informesAprobados = Informe::where('estado', 'aprobado')->count();
        $informesCorreccion = Informe::where('estado', 'correccion')->count();

        $estadisticasPorArea = Pasante::select('area', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('area')
            ->get();

        // Obtener actividad reciente global real
        $actividad = [];

        // 1. Informes recientes
        $informesRecientes = Informe::with('pasante.usuario')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
            
        foreach ($informesRecientes as $inf) {
            $actividad[] = [
                'id' => 'inf_' . $inf->id,
                'nombre' => ($inf->pasante->usuario->nombres ?? '') . ' ' . ($inf->pasante->usuario->apellidos ?? ''),
                'proyecto' => 'Informe ' . ($inf->tipo === 'final' ? 'Final' : 'Mensual'),
                'estado' => $inf->estado === 'aprobado' ? 'aprobado' : ($inf->estado === 'correccion' ? 'correccion' : 'en_espera'),
                'fecha' => $inf->created_at ? $inf->created_at->diffForHumans() : 'Hace poco',
                'timestamp' => $inf->created_at ? $inf->created_at->timestamp : 0
            ];
        }

        // 2. CVs recientes
        $cvsRecientes = \App\Models\CurriculumVitae::with('usuario')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($cvsRecientes as $cv) {
            $estadoStr = '';
            if ($cv->estado === 'validado') {
                $estadoStr = 'aprobado';
            } elseif ($cv->estado === 'rechazado') {
                $estadoStr = 'correccion';
            } else {
                $estadoStr = 'en_espera';
            }

            $actividad[] = [
                'id' => 'cv_' . $cv->id,
                'nombre' => ($cv->usuario->nombres ?? '') . ' ' . ($cv->usuario->apellidos ?? ''),
                'proyecto' => 'Curriculum Vitae',
                'estado' => $estadoStr,
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
            'metricas' => [
                'totalPasantes' => $totalPasantes,
                'totalSupervisores' => $totalSupervisores,
                'informesFinalesPendientes' => $informesFinalesPendientes,
                'informesAprobados' => $informesAprobados,
                'informesCorreccion' => $informesCorreccion
            ],
            'actividad' => $actividad,
            'estadisticasPorArea' => $estadisticasPorArea
        ]);
    }

    // Obtener la lista de informes finales pendientes
    public function getInformesFinales()
    {
        $informes = Informe::with('pasante.usuario')
            ->where('tipo', 'final')
            ->get()
            ->map(function ($i) {
                return (object)[
                    'id' => $i->id,
                    'pasante_id' => $i->pasante_id,
                    'nombre' => $i->nombre,
                    'tipo' => $i->tipo,
                    'archivo_url' => $i->archivo_url,
                    'estado' => $i->estado,
                    'horas' => $i->horas,
                    'observaciones' => $i->observaciones,
                    'objetivos' => $i->objetivos,
                    'actividades' => $i->actividades,
                    'conclusiones' => $i->conclusiones,
                    'created_at' => $i->created_at,
                    'fecha_inicio' => $i->fecha_inicio ? \Carbon\Carbon::parse($i->fecha_inicio)->format('d M Y') : null,
                    'fecha_fin' => $i->fecha_fin ? \Carbon\Carbon::parse($i->fecha_fin)->format('d M Y') : null,
                    'pasante_nombre' => $i->pasante->usuario->nombres ?? '',
                    'pasante_apellido' => $i->pasante->usuario->apellidos ?? '',
                    'area' => $i->pasante->area ?? '',
                    'bitacora' => $i->bitacora,
                    'imagenes' => $i->imagenes
                ];
            });

        return response()->json(['informes' => $informes]);
    }

    // Evaluar / Aprobar informe final
    public function evaluarInforme(Request $request, $id)
    {
        $request->validate([
            'veredicto' => 'required|in:aprobado,rechazado',
            'observaciones' => 'nullable|string'
        ]);

        $estado = $request->veredicto;
        $observaciones = $request->observaciones;

        $informe = Informe::find($id);
        if (!$informe) {
            return response()->json(['mensaje' => 'Informe no encontrado.'], 404);
        }

        $informe->update([
            'estado' => $estado,
            'observaciones' => $observaciones
        ]);

        $mensaje = $estado === 'aprobado' 
            ? 'Informe final aprobado. Se puede emitir carta de finalización.'
            : 'Informe final rechazado. Se solicitarán correcciones al estudiante.';

        return response()->json(['mensaje' => $mensaje]);
    }

    // Crear una nueva vacante
    public function crearVacante(Request $request)
    {
        $request->validate([
            'empresa' => 'required|string|max:255',
            'area' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'estado' => 'required|in:activa,inactiva'
        ]);

        $vacante = \App\Models\Vacante::create([
            'empresa' => $request->empresa,
            'area' => $request->area,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
        ]);

        return response()->json(['mensaje' => 'Vacante creada exitosamente.', 'vacante' => $vacante], 201);
    }

    // Obtener pasantes y supervisores para asignación
    public function getAsignacionesData()
    {
        $pasantes = Pasante::with('usuario')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombres' => $p->usuario->nombres ?? '',
                    'apellidos' => $p->usuario->apellidos ?? '',
                    'correo' => $p->usuario->correo_institucional ?? '',
                    'area' => $p->area,
                    'supervisor_id' => $p->supervisor_id
                ];
            });

        $supervisores = PersonalAdministrativo::where('cargo', 'supervisor')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nombres' => $s->nombres,
                    'apellidos' => $s->apellidos,
                    'correo' => $s->correo_institucional
                ];
            });

        return response()->json([
            'pasantes' => $pasantes,
            'supervisores' => $supervisores
        ]);
    }

    // Asignar supervisor a un pasante
    public function asignarSupervisor(Request $request)
    {
        $request->validate([
            'pasante_id' => 'required|integer',
            'supervisor_id' => 'required|integer'
        ]);

        $pasante = Pasante::find($request->pasante_id);
        if (!$pasante) {
            return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        }

        $supervisor = PersonalAdministrativo::where('id', $request->supervisor_id)
            ->where('cargo', 'supervisor')
            ->first();
        if (!$supervisor) {
            return response()->json(['mensaje' => 'Supervisor no encontrado o no tiene el cargo de supervisor.'], 404);
        }

        $pasante->update([
            'supervisor_id' => $request->supervisor_id
        ]);

        return response()->json(['mensaje' => 'Supervisor asignado exitosamente.']);
    }

    // Obtener todas las postulaciones del sistema
    public function getPostulaciones(Request $request)
    {
        $postulaciones = Postulacion::with(['pasante.usuario', 'vacante', 'pasante.supervisor', 'cv'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($post) {
                $supervisorNombre = 'No asignado';
                if ($post->pasante && $post->pasante->supervisor) {
                    $supervisorNombre = $post->pasante->supervisor->nombres . ' ' . $post->pasante->supervisor->apellidos;
                }

                return (object)[
                    'id' => $post->id,
                    'pasante_id' => $post->pasante_id,
                    'pasante_nombre' => ($post->pasante && $post->pasante->usuario) 
                        ? ($post->pasante->usuario->nombres . ' ' . $post->pasante->usuario->apellidos) 
                        : 'Estudiante',
                    'empresa' => $post->vacante->empresa ?? 'Empresa',
                    'area' => $post->vacante->area ?? '',
                    'vacante_id' => $post->vacante_id,
                    'estado' => $post->estado, // 'pendiente', 'sugerida', 'aprobado_por_supervisor', 'aceptada', 'rechazada'
                    'cv_url' => $post->cv ? $post->cv->url_publica : null,
                    'cv_titulo' => $post->cv ? ($post->cv->titulo_cv ?? 'CV') : 'CV',
                    'supervisor' => $supervisorNombre,
                    'fecha' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->format('d M Y') : 'Hace poco'
                ];
            });

        return response()->json(['postulaciones' => $postulaciones]);
    }

    // Evaluar postulación por el vicedecano (aceptar / rechazar)
    public function evaluarPostulacion(Request $request, $id)
    {
        $request->validate([
            'veredicto' => 'required|in:aceptar,rechazar'
        ]);

        $postulacion = Postulacion::find($id);
        if (!$postulacion) {
            return response()->json(['mensaje' => 'Postulación no encontrada.'], 404);
        }

        if ($request->veredicto === 'aceptar') {
            $postulacion->update(['estado' => 'aceptada']);

            // Si es aprobada, el pasante avanza a Fase 3 (F3)
            $pasante = Pasante::find($postulacion->pasante_id);
            if ($pasante) {
                $pasante->update(['fase_actual' => 'F3']);
            }

            return response()->json(['mensaje' => 'Postulación aprobada con éxito. El pasante avanza a Fase 3.']);
        } else {
            $postulacion->update(['estado' => 'rechazada']);
            return response()->json(['mensaje' => 'Postulación rechazada.']);
        }
    }
}

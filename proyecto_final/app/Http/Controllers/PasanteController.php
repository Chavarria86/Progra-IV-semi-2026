<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Pasante;
use App\Models\Informe;
use App\Models\Vacante;
use App\Models\Postulacion;
use App\Models\CurriculumVitae;
use Illuminate\Support\Facades\Storage;

class PasanteController extends Controller
{
    // Obtener información del perfil
    public function getPerfil(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4); // ID 4 es el primer pasante según seed

        $usuario = Usuario::with('pasante')->find($usuario_id);

        if (!$usuario || !$usuario->pasante) {
            return response()->json(['mensaje' => 'Perfil de pasante no encontrado.'], 404);
        }

        $perfil = (object)[
            'id' => $usuario->id,
            'nombre' => $usuario->nombres,
            'apellido' => $usuario->apellidos,
            'correo' => $usuario->correo_institucional,
            'pasante_id' => $usuario->pasante->id,
            'area' => $usuario->pasante->area,
            'tipo_pasantia' => $usuario->pasante->tipo_pasantia,
            'estado' => $usuario->pasante->estado,
            'fase_actual' => $usuario->pasante->fase_actual,
            'horas_aprobadas' => (float) $usuario->pasante->horas_aprobadas,
            'horas_pendientes' => (float) Informe::where('pasante_id', $usuario->pasante->id)->where('estado', 'en_espera')->sum('horas')
        ];

        $cv = CurriculumVitae::where('usuario_id', $usuario->id)->first();

        return response()->json([
            'perfil' => $perfil,
            'cv' => $cv
        ]);
    }

    // Obtener todos los CVs del pasante (para selección en postulaciones)
    public function getMisCvs(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $cvs = CurriculumVitae::where('usuario_id', $usuario_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($cv) {
                return [
                    'id'         => $cv->id,
                    'titulo'     => $cv->titulo_cv ?? 'CV sin título',
                    'created_at' => $cv->created_at,
                ];
            });
        return response()->json(['cvs' => $cvs]);
    }

    // Subir o actualizar CV
    public function subirCV(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        
        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);

        $archivo = "dummy_file_url.pdf"; 

        $existeCv = CurriculumVitae::where('usuario_id', $usuario_id)->first();

        if ($existeCv) {
            $existeCv->update(['ruta_archivo' => $archivo, 'estado' => 'activo']);
        } else {
            CurriculumVitae::create([
                'usuario_id' => $usuario_id,
                'ruta_archivo' => $archivo,
                'estado' => 'activo',
                'titulo_cv' => 'Mi CV'
            ]);
        }

        $pasante->update(['fase_actual' => 'F1']);

        return response()->json(['mensaje' => 'CV subido exitosamente']);
    }

    // Subir informe mensual o final
    public function subirInforme(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $request->validate([
            'tipo' => 'required|in:parcial,final',
            'horas' => 'required|numeric|min:0.01',
            'archivo' => 'nullable|file|mimes:pdf|max:10240',
            'nombre' => 'nullable|string|max:255',
            'objetivos' => 'nullable|string',
            'actividades' => 'nullable|string',
            'conclusiones' => 'nullable|string'
        ]);

        $usuario_id = $request->header('X-User-Id', 4); // Pasante

        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);

        $urlDocumento = null;
        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('informes', 'public');
            $urlDocumento = '/storage/' . $path;
        } else {
            // Si el frontend envía texto en lugar de un archivo PDF
            $nombreArchivo = 'informe_generado_' . time() . '.pdf';
            $ruta = "informes/{$nombreArchivo}";
            
            $nombreVal = $request->nombre ?: 'Informe de Pasantias';
            $horasVal = $request->horas;
            $tipoVal = $request->tipo === 'final' ? 'Informe Final' : 'Informe Mensual';
            $objVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->objetivos ?? ''), 0, 80);
            $actVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->actividades ?? ''), 0, 80);
            $conVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->conclusiones ?? ''), 0, 80);

            $pdfContent = "%PDF-1.4\n" .
                "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n" .
                "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n" .
                "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << >> /Contents 4 0 R >>\nendobj\n" .
                "4 0 obj\n<< /Length 300 >>\nstream\nBT\n/F1 14 Tf\n50 750 Td\n({$nombreVal}) Tj\n0 -25 Td\n/F1 12 Tf\n(Tipo de Informe: {$tipoVal}) Tj\n0 -20 Td\n(Horas Reportadas: {$horasVal} horas) Tj\n0 -30 Td\n(1. Objetivos del Periodo:) Tj\n0 -15 Td\n({$objVal}) Tj\n0 -30 Td\n(2. Actividades Realizadas:) Tj\n0 -15 Td\n({$actVal}) Tj\n0 -30 Td\n(3. Conclusiones y Logros:) Tj\n0 -15 Td\n({$conVal}) Tj\nET\nendstream\nendobj\n" .
                "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000213 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n490\n%%EOF";
                
            Storage::disk('public')->put($ruta, $pdfContent);
            $urlDocumento = "/storage/{$ruta}";
        }

        $informe = Informe::create([
            'pasante_id' => $pasante->id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'horas' => $request->horas,
            'archivo_url' => $urlDocumento,
            'estado' => 'en_espera',
            'objetivos' => $request->objetivos,
            'actividades' => $request->actividades,
            'conclusiones' => $request->conclusiones
        ]);

        return response()->json(['mensaje' => 'Informe subido correctamente. En espera de revisión.', 'informe' => $informe]);
    }

    // Editar un informe existente
    public function actualizarInforme(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:parcial,final',
            'horas' => 'required|numeric|min:0.01',
            'archivo' => 'nullable|file|mimes:pdf|max:10240',
            'nombre' => 'nullable|string|max:255',
            'objetivos' => 'nullable|string',
            'actividades' => 'nullable|string',
            'conclusiones' => 'nullable|string'
        ]);

        $usuario_id = $request->header('X-User-Id', 4);
        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) {
            return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        }

        $informe = Informe::where('id', $id)->where('pasante_id', $pasante->id)->first();
        if (!$informe) {
            return response()->json(['mensaje' => 'Informe no encontrado.'], 404);
        }

        if ($informe->estado === 'aprobado') {
            return response()->json(['mensaje' => 'No puedes modificar un informe ya aprobado.'], 403);
        }

        $datosUpdate = [
            'tipo' => $request->tipo,
            'horas' => $request->horas,
            'estado' => 'en_espera', // Si estaba en corrección, vuelve a revisión
            'nombre' => $request->nombre,
            'objetivos' => $request->objetivos,
            'actividades' => $request->actividades,
            'conclusiones' => $request->conclusiones
        ];

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('informes', 'public');
            $datosUpdate['archivo_url'] = '/storage/' . $path;
        } else {
            // Si el frontend edita pero no proporciona un archivo, actualizamos el PDF generado para reflejar los nuevos datos
            $nombreArchivo = 'informe_generado_' . time() . '.pdf';
            $ruta = "informes/{$nombreArchivo}";
            
            $nombreVal = $request->nombre ?: 'Informe de Pasantias';
            $horasVal = $request->horas;
            $tipoVal = $request->tipo === 'final' ? 'Informe Final' : 'Informe Mensual';
            $objVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->objetivos ?? ''), 0, 80);
            $actVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->actividades ?? ''), 0, 80);
            $conVal = substr(str_replace(["\r", "\n", "(", ")"], " ", $request->conclusiones ?? ''), 0, 80);

            $pdfContent = "%PDF-1.4\n" .
                "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n" .
                "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n" .
                "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << >> /Contents 4 0 R >>\nendobj\n" .
                "4 0 obj\n<< /Length 300 >>\nstream\nBT\n/F1 14 Tf\n50 750 Td\n({$nombreVal}) Tj\n0 -25 Td\n/F1 12 Tf\n(Tipo de Informe: {$tipoVal}) Tj\n0 -20 Td\n(Horas Reportadas: {$horasVal} horas) Tj\n0 -30 Td\n(1. Objetivos del Periodo:) Tj\n0 -15 Td\n({$objVal}) Tj\n0 -30 Td\n(2. Actividades Realizadas:) Tj\n0 -15 Td\n({$actVal}) Tj\n0 -30 Td\n(3. Conclusiones y Logros:) Tj\n0 -15 Td\n({$conVal}) Tj\nET\nendstream\nendobj\n" .
                "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000213 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n490\n%%EOF";
                
            // Eliminar anterior si existe y no es el base de seed
            if ($informe->archivo_url && !str_contains($informe->archivo_url, 'informe.pdf')) {
                $pathAnterior = str_replace('/storage/', '', $informe->archivo_url);
                Storage::disk('public')->delete($pathAnterior);
            }
            
            Storage::disk('public')->put($ruta, $pdfContent);
            $datosUpdate['archivo_url'] = "/storage/{$ruta}";
        }

        $informe->update($datosUpdate);

        return response()->json(['mensaje' => 'Informe actualizado correctamente.', 'informe' => $informe]);
    }

    // Eliminar un informe
    public function eliminarInforme(Request $request, $id)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) {
            return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        }
        
        $informe = Informe::where('id', $id)->where('pasante_id', $pasante->id)->first();
        if (!$informe) {
            return response()->json(['mensaje' => 'Informe no encontrado.'], 404);
        }

        if ($informe->estado === 'aprobado') {
            return response()->json(['mensaje' => 'No puedes eliminar un informe ya aprobado.'], 403);
        }

        // Eliminar archivo físico si existe y no es el base de seed
        if ($informe->archivo_url && !str_contains($informe->archivo_url, 'informe.pdf')) {
            $pathAnterior = str_replace('/storage/', '', $informe->archivo_url);
            Storage::disk('public')->delete($pathAnterior);
        }

        $informe->delete();

        return response()->json(['mensaje' => 'Informe eliminado exitosamente.']);
    }

    // Obtener informes subidos
    public function getInformes(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $pasante = Pasante::with('informes')->where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        
        return response()->json(['informes' => $pasante->informes]);
    }

    // Obtener información del supervisor asignado
    public function getMiSupervisor(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $pasante = Pasante::with('supervisor')->where('usuario_id', $usuario_id)->first();
        
        if (!$pasante || !$pasante->supervisor) {
            return response()->json(['supervisor' => null, 'notificaciones' => []]);
        }

        $supervisorInfo = [
            'nombres' => $pasante->supervisor->nombres,
            'apellidos' => $pasante->supervisor->apellidos,
            'correo' => $pasante->supervisor->correo_institucional,
            'cargo' => $pasante->supervisor->cargo,
        ];

        // Recopilar notificaciones de informes rechazados o CVs rechazados
        $notificaciones = [];
        $informesCorreccion = Informe::where('pasante_id', $pasante->id)->where('estado', 'correccion')->get();
        foreach ($informesCorreccion as $inf) {
            $notificaciones[] = [
                'tipo' => 'informe',
                'mensaje' => "El supervisor ha solicitado correcciones en el informe de " . $inf->horas . " horas.",
                'fecha' => $inf->updated_at->format('d M Y')
            ];
        }

        return response()->json([
            'supervisor' => $supervisorInfo,
            'notificaciones' => $notificaciones
        ]);
    }

    // Obtener vacantes disponibles
    public function getVacantes(Request $request)
    {
        $area = $request->query('area');

        $query = Vacante::where('estado', 'activa');
        if ($area) {
            $query->where('area', $area);
        }

        $vacantes = $query->orderByDesc('id')->get();

        return response()->json(['vacantes' => $vacantes]);
    }

    // Aplicar a una vacante
    public function aplicarVacante(Request $request, $id)
    {
        $usuario_id = $request->header('X-User-Id', 4);

        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) {
            return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        }

        $vacante = Vacante::where('id', $id)->where('estado', 'activa')->first();
        if (!$vacante) {
            return response()->json(['mensaje' => 'Vacante no disponible.'], 404);
        }

        // Verificar si ya existe postulación o sugerencia
        $postulacion = Postulacion::where('pasante_id', $pasante->id)
            ->where('vacante_id', $id)
            ->first();

        if ($postulacion) {
            if ($postulacion->estado === 'sugerida') {
                // Si ya fue sugerida, actualizamos el estado a 'pendiente' y asociamos el cv_id
                $postulacion->update([
                    'estado' => 'pendiente',
                    'cv_id' => $request->cv_id ?? null,
                    'created_at' => now() // Reset timestamp to now
                ]);
                return response()->json(['mensaje' => 'Postulación enviada exitosamente.']);
            }
            return response()->json(['mensaje' => 'Ya has aplicado a esta vacante.'], 409);
        }

        Postulacion::create([
            'pasante_id' => $pasante->id,
            'vacante_id' => $id,
            'estado'     => 'pendiente',
            'cv_id'      => $request->cv_id ?? null,
        ]);

        return response()->json(['mensaje' => 'Postulación enviada exitosamente.']);
    }

    // Obtener postulaciones del pasante
    public function getPostulaciones(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);

        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) {
            return response()->json(['postulaciones' => []]);
        }

        $postulaciones = Postulacion::with('vacante')
            ->where('pasante_id', $pasante->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                return (object)[
                    'id' => $p->id,
                    'estado' => $p->estado,
                    'created_at' => $p->created_at,
                    'empresa' => $p->vacante->empresa ?? '',
                    'area' => $p->vacante->area ?? '',
                    'descripcion' => $p->vacante->descripcion ?? '',
                    'vacante_id' => $p->vacante_id
                ];
            });

        return response()->json(['postulaciones' => $postulaciones]);
    }

    // Solicitar asignación a un supervisor
    public function solicitarSupervisor(Request $request)
    {
        $request->validate([
            'mensaje' => 'nullable|string|max:500'
        ]);

        $usuario_id = $request->header('X-User-Id', 4); // Pasante

        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) {
            return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);
        }

        // Buscar dinámicamente el primer supervisor disponible en personal_administrativo
        $supervisorAdmin = \App\Models\PersonalAdministrativo::where('cargo', 'supervisor')->first();
        if (!$supervisorAdmin) {
            return response()->json(['mensaje' => 'No hay supervisores disponibles en el sistema.'], 404);
        }
        $supervisor_id = $supervisorAdmin->id;

        // Checar si ya existe una solicitud pendiente
        $yaExiste = \DB::table('solicitudes_supervisor')
            ->where('pasante_id', $pasante->id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExiste) {
            return response()->json(['mensaje' => 'Ya tienes una solicitud pendiente.'], 409);
        }

        \DB::table('solicitudes_supervisor')->insert([
            'pasante_id'    => $pasante->id,
            'supervisor_id' => $supervisor_id,
            'mensaje'       => $request->mensaje ?? 'Solicito ser asignado a su supervisión.',
            'estado'        => 'pendiente',
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        return response()->json(['mensaje' => 'Solicitud enviada exitosamente al supervisor.']);
    }
}

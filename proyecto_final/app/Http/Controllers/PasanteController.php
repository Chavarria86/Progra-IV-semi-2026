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
use Barryvdh\DomPDF\Facade\Pdf;

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
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'bitacora' => 'nullable',
            'objetivos' => 'nullable|string',
            'actividades' => 'nullable|string',
            'conclusiones' => 'nullable|string',
            'imagenes' => 'nullable|array|max:4',
            'imagenes.*' => 'file|max:5120'
        ]);

        $pasante = Pasante::where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);

        $bitacora = $request->bitacora;
        if (is_string($bitacora)) {
            $bitacora = json_decode($bitacora, true);
        }
        if (!is_array($bitacora)) {
            $bitacora = [];
        }

        $urlsImagenes = [];
        if ($request->hasFile('imagenes')) {
            $files = $request->file('imagenes');
            foreach ($files as $file) {
                $path = $file->store('informes/images', 'public');
                $urlsImagenes[] = '/storage/' . $path;
            }
        }

        $informe = Informe::create([
            'pasante_id' => $pasante->id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'horas' => $request->horas,
            'archivo_url' => '', // se actualizará a continuación
            'estado' => 'en_espera',
            'objetivos' => $request->objetivos,
            'actividades' => $request->actividades,
            'conclusiones' => $request->conclusiones,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'bitacora' => $bitacora,
            'imagenes' => $urlsImagenes
        ]);

        $urlDocumento = null;
        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('informes', 'public');
            $urlDocumento = '/storage/' . $path;
        } else {
            $urlDocumento = "/api/informes/{$informe->id}/pdf";
        }

        $informe->update(['archivo_url' => $urlDocumento]);

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
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'bitacora' => 'nullable',
            'objetivos' => 'nullable|string',
            'actividades' => 'nullable|string',
            'conclusiones' => 'nullable|string',
            'imagenes' => 'nullable|array|max:4',
            'imagenes.*' => 'file|max:5120',
            'imagenes_existentes' => 'nullable'
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

        $bitacora = $request->bitacora;
        if (is_string($bitacora)) {
            $bitacora = json_decode($bitacora, true);
        }
        if (!is_array($bitacora)) {
            $bitacora = [];
        }

        $imagenesExistentes = $request->imagenes_existentes;
        if (is_string($imagenesExistentes)) {
            $imagenesExistentes = json_decode($imagenesExistentes, true);
        }
        if (!is_array($imagenesExistentes)) {
            $imagenesExistentes = [];
        }

        // Eliminar del almacenamiento físico las imágenes borradas
        $previousImages = $informe->imagenes ?? [];
        foreach ($previousImages as $prevImg) {
            if (!in_array($prevImg, $imagenesExistentes)) {
                $pathAnterior = str_replace('/storage/', '', $prevImg);
                Storage::disk('public')->delete($pathAnterior);
            }
        }

        $urlsImagenes = $imagenesExistentes;
        if ($request->hasFile('imagenes')) {
            $files = $request->file('imagenes');
            if (count($urlsImagenes) + count($files) > 4) {
                return response()->json(['mensaje' => 'No puedes subir más de 4 imágenes en total.'], 422);
            }
            foreach ($files as $file) {
                $path = $file->store('informes/images', 'public');
                $urlsImagenes[] = '/storage/' . $path;
            }
        }

        $datosUpdate = [
            'tipo' => $request->tipo,
            'horas' => $request->horas,
            'estado' => 'en_espera', // Si estaba en corrección, vuelve a revisión
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'bitacora' => $bitacora,
            'imagenes' => $urlsImagenes,
            'objetivos' => $request->objetivos,
            'actividades' => $request->actividades,
            'conclusiones' => $request->conclusiones
        ];

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('informes', 'public');
            $datosUpdate['archivo_url'] = '/storage/' . $path;

            if ($informe->archivo_url && str_contains($informe->archivo_url, 'informe_generado_')) {
                $pathAnterior = str_replace('/storage/', '', $informe->archivo_url);
                Storage::disk('public')->delete($pathAnterior);
            }
        } else {
            $datosUpdate['archivo_url'] = "/api/informes/{$informe->id}/pdf";

            if ($informe->archivo_url && str_contains($informe->archivo_url, 'informe_generado_')) {
                $pathAnterior = str_replace('/storage/', '', $informe->archivo_url);
                Storage::disk('public')->delete($pathAnterior);
            }
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
        
        $vicedecanoUser = Usuario::where('rol', 'vice_decano')->first();
        $vicedecanoInfo = null;
        if ($vicedecanoUser) {
            $vicedecanoInfo = [
                'usuario_id' => $vicedecanoUser->id,
                'nombres' => $vicedecanoUser->nombres,
                'apellidos' => $vicedecanoUser->apellidos,
                'correo' => $vicedecanoUser->correo_institucional,
                'cargo' => 'Vicedecano'
            ];
        }

        if (!$pasante || !$pasante->supervisor) {
            return response()->json([
                'supervisor' => null, 
                'vicedecano' => $vicedecanoInfo,
                'notificaciones' => []
            ]);
        }

        $supervisorUser = Usuario::where('correo_institucional', $pasante->supervisor->correo_institucional)->first();
        $supervisorInfo = [
            'usuario_id' => $supervisorUser ? $supervisorUser->id : null,
            'nombres' => $pasante->supervisor->nombres,
            'apellidos' => $pasante->supervisor->apellidos,
            'correo' => $pasante->supervisor->correo_institucional,
            'cargo' => $pasante->supervisor->cargo,
        ];

        // Recopilar notificaciones de informes rechazados o CVs rechazados
        $notificaciones = [];
        $informesCorreccion = Informe::where('pasante_id', $pasante->id)->where('estado', 'correccion')->get();
        foreach ($informesCorreccion as $inf) {
            $fecha = 'Reciente';
            if ($inf->updated_at) {
                $fecha = ($inf->updated_at instanceof \Carbon\Carbon || $inf->updated_at instanceof \DateTime)
                    ? $inf->updated_at->format('d M Y')
                    : date('d M Y', strtotime($inf->updated_at));
            } elseif ($inf->created_at) {
                $fecha = ($inf->created_at instanceof \Carbon\Carbon || $inf->created_at instanceof \DateTime)
                    ? $inf->created_at->format('d M Y')
                    : date('d M Y', strtotime($inf->created_at));
            }
            $notificaciones[] = [
                'tipo' => 'informe',
                'mensaje' => "El supervisor ha solicitado correcciones en el informe de " . $inf->horas . " horas.",
                'fecha' => $fecha
            ];
        }

        return response()->json([
            'supervisor' => $supervisorInfo,
            'vicedecano' => $vicedecanoInfo,
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

    // Redimensionar imágenes de evidencia si exceden el límite de tamaño para optimizar DomPDF
    private function resizeImageIfNeeded($absPath, $maxDimension = 800)
    {
        if (!file_exists($absPath)) {
            return [null, null];
        }

        $info = @getimagesize($absPath);
        if (!$info) {
            return [file_get_contents($absPath), pathinfo($absPath, PATHINFO_EXTENSION)];
        }

        list($width, $height, $type) = $info;
        $extension = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        $im = null;
        if ($extension === 'jpg' || $extension === 'jpeg' || $type === IMAGETYPE_JPEG) {
            $im = @imagecreatefromjpeg($absPath);
            $extension = 'jpeg';
        } elseif ($extension === 'png' || $type === IMAGETYPE_PNG) {
            $im = @imagecreatefrompng($absPath);
            $extension = 'png';
        } elseif ($extension === 'webp' || (defined('IMAGETYPE_WEBP') && $type === IMAGETYPE_WEBP)) {
            if (function_exists('imagecreatefromwebp')) {
                $im = @imagecreatefromwebp($absPath);
                $extension = 'webp';
            }
        }

        if (!$im) {
            return [file_get_contents($absPath), $extension];
        }

        if ($width <= $maxDimension && $height <= $maxDimension && $extension !== 'webp') {
            imagedestroy($im);
            return [file_get_contents($absPath), $extension];
        }

        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) round($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) round($width * ($maxDimension / $height));
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);

        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        imagecopyresampled($dst, $im, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagepng($dst);
        $data = ob_get_clean();

        imagedestroy($im);
        imagedestroy($dst);

        return [$data, 'png'];
    }

    // Generar y transmitir el PDF en tiempo real (on-the-fly)
    public function verPdf($id)
    {
        $informe = Informe::find($id);
        if (!$informe) {
            abort(404, 'Informe no encontrado.');
        }

        $pasante = Pasante::find($informe->pasante_id);
        if (!$pasante) {
            abort(404, 'Pasante no encontrado.');
        }

        // Cargar Logo UGB en ruta absoluta local y convertir a Base64
        $logoPath = public_path('images/logo_ugb.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
        }

        // Obtener rutas absolutas locales de las imágenes de evidencia y convertirlas a Base64 (redimensionando si es necesario)
        $imagenesBase64 = [];
        if (!empty($informe->imagenes)) {
            foreach ($informe->imagenes as $imgUrl) {
                $relativePath = str_replace('/storage/', '', $imgUrl);
                $absPath = storage_path('app/public/' . $relativePath);
                if (file_exists($absPath)) {
                    list($data, $ext) = $this->resizeImageIfNeeded($absPath, 800);
                    if ($data) {
                        $imagenesBase64[] = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                    }
                }
            }
        }

        $pdf = Pdf::loadView('pdf.informe', [
            'informe' => $informe,
            'pasante' => $pasante->load('usuario'),
            'bitacora' => $informe->bitacora ?? [],
            'logoBase64' => $logoBase64,
            'imagenesBase64' => $imagenesBase64
        ]);

        return $pdf->stream('informe_' . $informe->id . '.pdf');
    }
}

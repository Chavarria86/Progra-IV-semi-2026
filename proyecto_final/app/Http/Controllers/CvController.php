<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CurriculumVitae;

class CvController extends Controller
{
    /**
     * Guardar un NUEVO CV para un usuario (permite múltiples CVs por usuario).
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'usuario_id'  => 'required|integer',
            'pdf_base64'  => 'required|string',
            'titulo_cv'   => 'nullable|string|max:255',
            'cv_id'       => 'nullable|integer',
        ]);

        $usuarioId = $request->usuario_id;
        $cvId = $request->cv_id;

        // Verificar que el usuario existe en la tabla 'usuarios'
        $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Decodificar el PDF de base64 (removiendo cualquier prefijo de URI de datos de forma segura)
        $pdfData = base64_decode(preg_replace('#^data:.*?base64,#i', '', $request->pdf_base64));

        // Nombre del archivo basado en el título del CV
        $tituloCv   = $request->titulo_cv ?? ('CV_' . ($usuario->nombres ?? 'Usuario'));
        $nombreArchivo = str_replace(' ', '_', $tituloCv) . '_' . $usuarioId . '_' . time() . '.pdf';

        // Ruta: storage/app/public/cvs/{usuario_id}/
        $carpeta  = "cvs/{$usuarioId}";
        $ruta     = "{$carpeta}/{$nombreArchivo}";

        // Guardar en disco
        Storage::disk('public')->put($ruta, $pdfData);

        $urlPublica = "/storage/{$ruta}";

        $data = [
            'usuario_id'       => $usuarioId,
            'titulo_cv'        => $tituloCv,
            'nombre_archivo'   => $nombreArchivo,
            'ruta_archivo'     => $ruta,
            'url_publica'      => $urlPublica,
            'nombre_completo'  => $request->perfil['nombre']          ?? null,
            'direccion'        => $request->perfil['direccion']        ?? null,
            'email'            => $request->perfil['email']            ?? null,
            'telefono'         => $request->perfil['telefono']         ?? null,
            'sobre_mi'         => $request->perfil['sobreMi']          ?? null,
            'educacion'        => $request->perfil['educacion']        ?? null,
            'objetivo'         => $request->objetivos['objetivo']      ?? null,
            'valores'          => $request->objetivos['valores']       ?? null,
            'conocimientos'    => $request->objetivos['conocimientos'] ?? null,
            'idiomas'          => $request->objetivos['idiomas']       ?? null,
            'certificados'     => $request->logros['certificados']     ?? null,
            'habilidades'      => $request->logros['habilidades']      ?? null,
            'logros'           => $request->logros['logros']           ?? null,
            'proyectos_sociales' => $request->logros['proyectos']      ?? null,
            'color_plantilla'  => $request->diseno['color']            ?? '#67000F',
            'fuente'           => $request->diseno['fuente']           ?? 'Montserrat',
            'estado'           => 'activo',
        ];

        if ($cvId) {
            $cv = CurriculumVitae::find($cvId);
            if ($cv) {
                // Eliminar archivo físico anterior si existe
                if ($cv->ruta_archivo) {
                    Storage::disk('public')->delete($cv->ruta_archivo);
                }
                $cv->update($data);
            } else {
                $cv = CurriculumVitae::create($data);
            }
        } else {
            $cv = CurriculumVitae::create($data);
        }

        return response()->json([
            'mensaje'        => 'CV guardado correctamente.',
            'cv_id'          => $cv->id,
            'url_publica'    => $urlPublica,
            'nombre_archivo' => $nombreArchivo,
            'titulo_cv'      => $tituloCv,
        ]);
    }

    /**
     * Obtener TODOS los CVs de un usuario.
     */
    public function obtener(Request $request, $usuarioId)
    {
        $cvs = CurriculumVitae::where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();

        if ($cvs->isEmpty()) {
            return response()->json(['mensaje' => 'Este usuario aún no tiene CV.', 'tiene_cv' => false, 'cvs' => []], 200);
        }

        return response()->json([
            'tiene_cv' => true,
            'cvs'      => $cvs,
        ]);
    }

    /**
     * Eliminar un CV específico por su ID.
     */
    public function eliminar(Request $request, $cvId)
    {
        $cv = CurriculumVitae::find($cvId);

        if (!$cv) {
            return response()->json(['mensaje' => 'No se encontró el CV.'], 404);
        }

        // Eliminar archivo físico
        Storage::disk('public')->delete($cv->ruta_archivo);

        $cv->delete();

        return response()->json(['mensaje' => 'CV eliminado correctamente.']);
    }
}

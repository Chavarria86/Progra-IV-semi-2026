<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CurriculumVitae;

class CvController extends Controller
{
    /**
     * Guardar o actualizar el CV de un usuario.
     * Solo el propio usuario puede guardar su CV.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'usuario_id'  => 'required|integer',
            'pdf_base64'  => 'required|string',   // PDF en base64 desde el frontend
            'nombre'      => 'nullable|string|max:255',
        ]);

        $usuarioId = $request->usuario_id;

        // Verificar que el usuario existe en la tabla 'usuarios'
        $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Decodificar el PDF de base64
        $pdfData = base64_decode(preg_replace('#^data:application/pdf;base64,#i', '', $request->pdf_base64));

        // Nombre del archivo
        $nombreArchivo = 'CV_' . str_replace(' ', '_', $usuario->nombres ?? 'Usuario') . '_' . $usuarioId . '.pdf';

        // Ruta: storage/app/public/cvs/{usuario_id}/
        $carpeta  = "cvs/{$usuarioId}";
        $ruta     = "{$carpeta}/{$nombreArchivo}";

        // Guardar en disco (sobreescribe si ya existe)
        Storage::disk('public')->put($ruta, $pdfData);

        $urlPublica = asset("storage/{$ruta}");

        // Crear o actualizar el registro (un usuario = un CV)
        $cv = CurriculumVitae::updateOrCreate(
            ['usuario_id' => $usuarioId],
            [
                'nombre_archivo'   => $nombreArchivo,
                'ruta_archivo'     => $ruta,
                'url_publica'      => $urlPublica,
                'nombre_completo'  => $request->perfil['nombre']       ?? null,
                'direccion'        => $request->perfil['direccion']     ?? null,
                'email'            => $request->perfil['email']         ?? null,
                'telefono'         => $request->perfil['telefono']      ?? null,
                'sobre_mi'         => $request->perfil['sobreMi']       ?? null,
                'educacion'        => $request->perfil['educacion']     ?? null,
                'objetivo'         => $request->objetivos['objetivo']   ?? null,
                'valores'          => $request->objetivos['valores']    ?? null,
                'conocimientos'    => $request->objetivos['conocimientos'] ?? null,
                'idiomas'          => $request->objetivos['idiomas']    ?? null,
                'certificados'     => $request->logros['certificados']  ?? null,
                'habilidades'      => $request->logros['habilidades']   ?? null,
                'logros'           => $request->logros['logros']        ?? null,
                'proyectos_sociales' => $request->logros['proyectos']   ?? null,
                'color_plantilla'  => $request->diseno['color']         ?? '#67000F',
                'fuente'           => $request->diseno['fuente']        ?? 'Montserrat',
                'estado'           => 'activo',
            ]
        );

        return response()->json([
            'mensaje'     => 'CV guardado correctamente.',
            'cv_id'       => $cv->id,
            'url_publica' => $urlPublica,
            'nombre_archivo' => $nombreArchivo,
        ]);
    }

    /**
     * Obtener el CV de un usuario específico.
     * Solo devuelve datos si el usuario_id coincide.
     */
    public function obtener(Request $request, $usuarioId)
    {
        $cv = CurriculumVitae::where('usuario_id', $usuarioId)->first();

        if (!$cv) {
            return response()->json(['mensaje' => 'Este usuario aún no tiene CV.', 'tiene_cv' => false], 200);
        }

        return response()->json([
            'tiene_cv'   => true,
            'cv'         => $cv,
            'url_publica' => $cv->url_publica,
        ]);
    }

    /**
     * Eliminar el CV de un usuario.
     */
    public function eliminar(Request $request, $usuarioId)
    {
        $cv = CurriculumVitae::where('usuario_id', $usuarioId)->first();

        if (!$cv) {
            return response()->json(['mensaje' => 'No se encontró CV para este usuario.'], 404);
        }

        // Eliminar archivo físico
        Storage::disk('public')->delete($cv->ruta_archivo);

        $cv->delete();

        return response()->json(['mensaje' => 'CV eliminado correctamente.']);
    }
}

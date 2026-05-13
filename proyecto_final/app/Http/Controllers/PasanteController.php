<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasanteController extends Controller
{
    // Obtener información del perfil
    public function getPerfil(Request $request)
    {
        // El ID del usuario pasante se debería obtener de la sesión/token
        // Por simplicidad en la simulación, asumimos un ID harcodeado o enviado en headers
        // si no hay auth implementado completamente con Sanctum.
        $usuario_id = $request->header('X-User-Id', 4); // ID 4 es el primer pasante según seed

        $perfil = DB::table('usuarios as u')
            ->join('pasantes as p', 'p.usuario_id', '=', 'u.id')
            ->where('u.id', $usuario_id)
            ->select('u.id', 'u.nombres as nombre', 'u.apellidos as apellido', 'u.correo_institucional as correo',
                     'p.id as pasante_id', 'p.area', 'p.tipo_pasantia', 'p.estado', 'p.fase_actual')
            ->first();

        if (!$perfil) {
            return response()->json(['mensaje' => 'Perfil de pasante no encontrado.'], 404);
        }

        $cv = DB::table('cv')->where('pasante_id', $perfil->pasante_id)->first();

        return response()->json([
            'perfil' => $perfil,
            'cv' => $cv
        ]);
    }

    // Subir o actualizar CV
    public function subirCV(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        
        $pasante = DB::table('pasantes')->where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);

        $archivo = "dummy_file_url.pdf"; // Lógica real de subida iría aquí con $request->file()

        $existeCv = DB::table('cv')->where('pasante_id', $pasante->id)->first();

        if ($existeCv) {
            DB::table('cv')
                ->where('pasante_id', $pasante->id)
                ->update(['archivo_url' => $archivo, 'estado' => 'subido', 'updated_at' => now()]);
        } else {
            DB::table('cv')->insert([
                'pasante_id' => $pasante->id,
                'archivo_url' => $archivo,
                'estado' => 'subido',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::table('pasantes')->where('id', $pasante->id)->update(['fase_actual' => 'F1']);

        return response()->json(['mensaje' => 'CV subido exitosamente']);
    }

    // Subir informe mensual o final
    public function subirInforme(Request $request)
    {
        $usuario_id = $request->header('X-User-Id', 4);
        $tipo = $request->tipo ?? 'mensual';

        $pasante = DB::table('pasantes')->where('usuario_id', $usuario_id)->first();
        if (!$pasante) return response()->json(['mensaje' => 'Pasante no encontrado.'], 404);

        DB::table('informes')->insert([
            'pasante_id' => $pasante->id,
            'tipo' => $tipo,
            'archivo_url' => "informe_{$tipo}.pdf",
            'estado' => 'en_espera',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Informe subido exitosamente']);
    }

    // Obtener vacantes disponibles
    public function getVacantes(Request $request)
    {
        $area = $request->query('area');
        
        $query = DB::table('vacantes')->where('estado', 'activa');
        if ($area) {
            $query->where('area', $area);
        }

        $vacantes = $query->orderByDesc('created_at')->get();

        return response()->json(['vacantes' => $vacantes]);
    }
}

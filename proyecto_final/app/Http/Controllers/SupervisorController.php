<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    // Obtener los pasantes asignados a este supervisor
    public function getPasantes(Request $request)
    {
        $supervisor_id = $request->header('X-User-Id', 2); // Simulado

        $pasantes = DB::table('pasantes as p')
            ->join('usuarios as u', 'u.id', '=', 'p.usuario_id')
            ->where('p.supervisor_id', $supervisor_id)
            ->select('u.nombres as nombre', 'u.apellidos as apellido', 'u.correo_institucional as correo',
                     'p.id as pasante_id', 'p.area', 'p.estado', 'p.fase_actual')
            ->get();

        return response()->json(['pasantes' => $pasantes]);
    }

    // Validar el CV de un pasante
    public function validarCV(Request $request, $id)
    {
        DB::table('cv')
            ->where('id', $id)
            ->update(['estado' => 'validado', 'updated_at' => now()]);

        $cv = DB::table('cv')->where('id', $id)->first();
        if ($cv) {
            DB::table('pasantes')
                ->where('id', $cv->pasante_id)
                ->update(['fase_actual' => 'F2']);
        }

        return response()->json(['mensaje' => 'CV validado exitosamente. Pasante avanza a Fase 2.']);
    }

    // Rechazar el CV de un pasante
    public function rechazarCV(Request $request, $id)
    {
        $request->validate(['observaciones' => 'required|string']);

        DB::table('cv')
            ->where('id', $id)
            ->update([
                'estado' => 'rechazado', 
                'observaciones' => $request->observaciones,
                'updated_at' => now()
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

        $existe = DB::table('postulaciones')
            ->where('pasante_id', $pasante_id)
            ->where('vacante_id', $vacante_id)
            ->first();

        if ($existe) {
            DB::table('postulaciones')
                ->where('id', $existe->id)
                ->update(['estado' => 'aceptada', 'updated_at' => now()]);
        } else {
            DB::table('postulaciones')->insert([
                'pasante_id' => $pasante_id,
                'vacante_id' => $vacante_id,
                'estado' => 'aceptada',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::table('pasantes')->where('id', $pasante_id)->update(['fase_actual' => 'F3']);

        return response()->json(['mensaje' => 'Pasante asignado exitosamente. Avanza a Fase 3.']);
    }
}

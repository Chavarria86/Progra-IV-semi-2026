<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViceDecanoController extends Controller
{
    // Obtener estadísticas y métricas
    public function getDashboard()
    {
        $totalPasantes = DB::table('pasantes')->count();
        $totalSupervisores = DB::table('personal_administrativo')->where('cargo', 'NOT LIKE', '%decano%')->count();
        $informesFinalesPendientes = DB::table('informes')->where('tipo', 'final')->where('estado', 'en_espera')->count();

        $estadisticasPorArea = DB::table('pasantes')
            ->select('area', DB::raw('COUNT(*) as total'))
            ->groupBy('area')
            ->get();

        return response()->json([
            'metricas' => [
                'totalPasantes' => $totalPasantes,
                'totalSupervisores' => $totalSupervisores,
                'informesFinalesPendientes' => $informesFinalesPendientes
            ],
            'estadisticasPorArea' => $estadisticasPorArea
        ]);
    }

    // Obtener la lista de informes finales pendientes
    public function getInformesFinales()
    {
        $informes = DB::table('informes as i')
            ->join('pasantes as p', 'p.id', '=', 'i.pasante_id')
            ->join('usuarios as u', 'u.id', '=', 'p.usuario_id')
            ->where('i.tipo', 'final')
            ->select('i.*', 'u.nombres as pasante_nombre', 'u.apellidos as pasante_apellido', 'p.area')
            ->orderByDesc('i.created_at')
            ->get();

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

        DB::table('informes')
            ->where('id', $id)
            ->update([
                'estado' => $estado,
                'observaciones' => $observaciones,
                'updated_at' => now()
            ]);

        $mensaje = $estado === 'aprobado' 
            ? 'Informe final aprobado. Se puede emitir carta de finalización.'
            : 'Informe final rechazado. Se solicitarán correcciones al estudiante.';

        return response()->json(['mensaje' => $mensaje]);
    }
}

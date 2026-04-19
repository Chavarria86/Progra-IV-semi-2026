<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscripcion;

class InscripcionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inscripcion::query();
        
        if ($request->has('buscar')) {
            $busqueda = $request->get('buscar');
            $query->where('idAlumno', 'like', "%{$busqueda}%")
                  ->orWhere('idMateria', 'like', "%{$busqueda}%");
        }

        return response()->json($query->orderBy('idInscripcion', 'desc')->get(), 200);
    }

    public function create()
    {
        return view('inscripciones.vue');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idAlumno' => 'required',
            'idMateria' => 'required',
            'fecha' => 'required|date'
        ]);

        $inscripcion = Inscripcion::create($validatedData);

        return response()->json(['message' => 'Inscripción creada', 'inscripcion' => $inscripcion], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $inscripcion = Inscripcion::where('idInscripcion', $id)->first();
        if(!$inscripcion) return response()->json(['message' => 'No encontrado'], 404);

        $validatedData = $request->validate([
            'idAlumno' => 'required',
            'idMateria' => 'required',
            'fecha' => 'required|date'
        ]);

        $inscripcion->update($validatedData);

        return response()->json(['message' => 'Inscripción actualizada', 'inscripcion' => $inscripcion], 200);
    }

    public function destroy(string $id)
    {
        $inscripcion = Inscripcion::where('idInscripcion', $id)->first();
        if(!$inscripcion) return response()->json(['message' => 'No encontrado'], 404);
        
        $inscripcion->delete();
        return response()->json(['message' => 'Inscripción eliminada'], 200);
    }
}

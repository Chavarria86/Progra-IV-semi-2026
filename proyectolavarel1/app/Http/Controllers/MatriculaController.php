<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Matricula;

class MatriculaController extends Controller
{
    public function index(Request $request)
    {
        $query = Matricula::query();
        
        if ($request->has('buscar')) {
            $busqueda = $request->get('buscar');
            $query->where('estado', 'like', "%{$busqueda}%")
                  ->orWhere('periodo', 'like', "%{$busqueda}%");
        }

        return response()->json($query->orderBy('id', 'desc')->get(), 200);
    }

    public function create()
    {
        return view('matriculas.vue');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idMatricula' => 'required',
            'idAlumno' => 'required|string|max:36',
            'idMateria' => 'required|string|max:36',
            'idDocente' => 'required|string|max:36',
            'fecha' => 'required|date',
            'estado' => 'required|max:20',
            'periodo' => 'required|max:20',
            'gestion' => 'required|numeric'
        ]);

        $matricula = Matricula::create($validatedData);
        return response()->json(['message' => 'Matricula creada', 'matricula' => $matricula], 201);
    }

    public function update(Request $request, string $id)
    {
        $matricula = Matricula::where('idMatricula', $id)->orWhere('id', $id)->first();
        if(!$matricula) return response()->json(['message' => 'No encontrado'], 404);

        $validatedData = $request->validate([
            'idAlumno' => 'required|string|max:36',
            'idMateria' => 'required|string|max:36',
            'idDocente' => 'required|string|max:36',
            'fecha' => 'required|date',
            'estado' => 'required|max:20',
            'periodo' => 'required|max:20',
            'gestion' => 'required|numeric'
        ]);

        $matricula->update($validatedData);
        return response()->json(['message' => 'Matricula actualizada', 'matricula' => $matricula], 200);
    }

    public function destroy(string $id)
    {
        $matricula = Matricula::where('idMatricula', $id)->orWhere('id', $id)->first();
        if(!$matricula) return response()->json(['message' => 'No encontrado'], 404);
        
        $matricula->delete();
        return response()->json(['message' => 'Matricula eliminada'], 200);
    }
}

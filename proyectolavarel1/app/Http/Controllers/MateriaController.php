<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;

class MateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Materia::query();
        
        if ($request->has('buscar')) {
            $busqueda = $request->get('buscar');
            $query->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%");
        }

        return response()->json($query->orderBy('id', 'desc')->get(), 200);
    }

    public function create()
    {
        return view('materias.vue');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idMateria' => 'required',
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'uv' => 'required|max:5'
        ]);

        $materia = Materia::create($validatedData);
        return response()->json(['message' => 'Materia creada', 'materia' => $materia], 201);
    }

    public function update(Request $request, string $id)
    {
        $materia = Materia::where('idMateria', $id)->orWhere('id', $id)->first();
        if(!$materia) return response()->json(['message' => 'No encontrado'], 404);

        $validatedData = $request->validate([
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'uv' => 'required|max:5'
        ]);

        $materia->update($validatedData);
        return response()->json(['message' => 'Materia actualizada', 'materia' => $materia], 200);
    }

    public function destroy(string $id)
    {
        $materia = Materia::where('idMateria', $id)->orWhere('id', $id)->first();
        if(!$materia) return response()->json(['message' => 'No encontrado'], 404);
        
        $materia->delete();
        return response()->json(['message' => 'Materia eliminada'], 200);
    }
}

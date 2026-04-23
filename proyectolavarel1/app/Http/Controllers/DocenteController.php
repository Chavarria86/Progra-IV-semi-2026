<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Docente;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        $query = Docente::query();
        
        if ($request->has('buscar')) {
            $busqueda = $request->get('buscar');
            $query->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('telefono', 'like', "%{$busqueda}%");
        }

        return response()->json($query->orderBy('id', 'desc')->get(), 200);
    }

    public function create()
    {
        return view('docentes.vue');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Id_Docentes' => 'required',
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'direccion' => 'required|max:150',
            'telefono' => 'required|max:10',
            'email' => 'required|email',
            'escalafon' => 'required|max:50'
        ]);

        $docente = Docente::create($validatedData);
        return response()->json(['message' => 'Docente creado', 'docente' => $docente], 201);
    }

    public function update(Request $request, string $id)
    {
        $docente = Docente::where('Id_Docentes', $id)->orWhere('id', $id)->first();
        if(!$docente) return response()->json(['message' => 'No encontrado'], 404);

        $validatedData = $request->validate([
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'direccion' => 'required|max:150',
            'telefono' => 'required|max:10',
            'email' => 'required|email',
            'escalafon' => 'required|max:50'
        ]);

        $docente->update($validatedData);
        return response()->json(['message' => 'Docente actualizado', 'docente' => $docente], 200);
    }

    public function destroy(string $id)
    {
        $docente = Docente::where('Id_Docentes', $id)->orWhere('id', $id)->first();
        if(!$docente) return response()->json(['message' => 'No encontrado'], 404);
        
        $docente->delete();
        return response()->json(['message' => 'Docente eliminado'], 200);
    }
}

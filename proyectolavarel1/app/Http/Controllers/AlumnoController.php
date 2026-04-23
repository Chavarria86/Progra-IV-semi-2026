<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Alumno::query();
        
        // Multi-field search support
        if ($request->has('buscar')) {
            $busqueda = $request->get('buscar');
            $query->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('telefono', 'like', "%{$busqueda}%");
        }

        return response()->json($query->orderBy('id', 'desc')->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Para vista web, pero también permitiremos nuestro Vue test
        return view('alumnos.vue');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idAlumno' => 'required',
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'direccion' => 'required|max:150',
            'telefono' => 'required|max:10',
            'email' => 'required|email|max:255'
        ]);

        // Creamos y guardamos el alumno 
        $alumno = \App\Models\Alumno::create($validatedData);

        return response()->json(['message' => 'Alumno creado', 'alumno' => $alumno], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $alumno = \App\Models\Alumno::where('idAlumno', $id)->orWhere('id', $id)->first();
        if(!$alumno) return response()->json(['message' => 'No encontrado'], 404);

        $validatedData = $request->validate([
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:100',
            'direccion' => 'required|max:150',
            'telefono' => 'required|max:10',
            'email' => 'required|email|max:255'
        ]);

        $alumno->update($validatedData);

        return response()->json(['message' => 'Alumno actualizado', 'alumno' => $alumno], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $alumno = \App\Models\Alumno::where('idAlumno', $id)->orWhere('id', $id)->first();
        if(!$alumno) return response()->json(['message' => 'No encontrado'], 404);
        
        $alumno->delete();
        return response()->json(['message' => 'Alumno eliminado'], 200);
    }
}

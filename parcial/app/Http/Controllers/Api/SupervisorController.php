<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $query = Supervisor::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('id', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'email' => 'required|email|unique:supervisores',
            'telefono' => 'nullable|string|max:20',
        ]);

        $supervisor = Supervisor::create($request->all());

        return response()->json(['message' => 'Supervisor creado exitosamente', 'data' => $supervisor], 201);
    }

    public function show(string $id)
    {
        $supervisor = Supervisor::findOrFail($id);
        return response()->json($supervisor);
    }

    public function update(Request $request, string $id)
    {
        $supervisor = Supervisor::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'email' => 'required|email|unique:supervisores,email,' . $supervisor->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $supervisor->update($request->all());

        return response()->json(['message' => 'Supervisor actualizado exitosamente', 'data' => $supervisor]);
    }

    public function destroy(string $id)
    {
        $supervisor = Supervisor::findOrFail($id);
        $supervisor->delete();

        return response()->json(['message' => 'Supervisor eliminado exitosamente']);
    }
}

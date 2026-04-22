<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasante;
use Illuminate\Http\Request;

class PasanteController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasante::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('carnet', 'like', "%{$search}%")
                  ->orWhere('carrera', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('id', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'carnet' => 'required|string|max:50|unique:pasantes',
            'carrera' => 'required|string|max:255',
            'email' => 'required|email|unique:pasantes',
            'telefono' => 'nullable|string|max:20',
        ]);

        $pasante = Pasante::create($request->all());

        return response()->json(['message' => 'Pasante creado exitosamente', 'data' => $pasante], 201);
    }

    public function show(string $id)
    {
        $pasante = Pasante::findOrFail($id);
        return response()->json($pasante);
    }

    public function update(Request $request, string $id)
    {
        $pasante = Pasante::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'carnet' => 'required|string|max:50|unique:pasantes,carnet,' . $pasante->id,
            'carrera' => 'required|string|max:255',
            'email' => 'required|email|unique:pasantes,email,' . $pasante->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $pasante->update($request->all());

        return response()->json(['message' => 'Pasante actualizado exitosamente', 'data' => $pasante]);
    }

    public function destroy(string $id)
    {
        $pasante = Pasante::findOrFail($id);
        $pasante->delete();

        return response()->json(['message' => 'Pasante eliminado exitosamente']);
    }
}

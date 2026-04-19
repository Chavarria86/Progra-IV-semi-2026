<?php

namespace App\Http\Controllers;

use App\Models\estudiantes; // Asegúrate que este modelo tenga los campos del SQL
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EstudiantesController extends Controller
{
    /**
     * Muestra la lista de todos los estudiantes registrados.
     */
    public function index()
    {
        $estudiantes = estudiantes::all();
        return view('estudiantes.index', compact('estudiantes'));
    }

    /**
     * Muestra el formulario para registrar un nuevo estudiante.
     */
    public function create()
    {
        return view('estudiantes.create');
    }

    /**
     * Guarda el nuevo estudiante en la base de datos SQLite.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos según tu estructura SQL
        $request->validate([
            'codigo' => 'required|unique:alumnos,codigo',
            'nombre' => 'required|max:100',
            'email'  => 'required|email'
        ]);

        // 2. Preparar los datos y generar el UUID (idAlumno)
        $datos = $request->all();
        $datos['idAlumno'] = (string) Str::uuid(); 

        // 3. Crear el registro
        estudiantes::create($datos);

        return redirect()->route('estudiantes.index')
                         ->with('success', 'Estudiante registrado correctamente.');
    }

    /**
     * Elimina un estudiante del sistema.
     */
    public function destroy(estudiantes $estudiante)
    {
        $estudiante->delete();
        return redirect()->route('estudiantes.index')
                         ->with('success', 'Registro eliminado.');
    }
}


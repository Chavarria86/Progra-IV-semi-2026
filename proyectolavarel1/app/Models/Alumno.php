<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';
    public $timestamps = false;

    protected $fillable = [
        'idAlumno', 'codigo', 'nombre', 'direccion', 'telefono', 'email'
    ];
}

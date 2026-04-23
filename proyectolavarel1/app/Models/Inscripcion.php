<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';
    protected $primaryKey = 'idInscripcion';
    public $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'idAlumno',
        'idMateria',
        'fecha'
    ];
}

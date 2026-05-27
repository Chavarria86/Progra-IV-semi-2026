<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumVitae extends Model
{
    protected $table = 'curriculum_vitae';

    protected $fillable = [
        'usuario_id',
        'titulo_cv',
        'nombre_archivo',
        'ruta_archivo',
        'url_publica',
        'nombre_completo',
        'direccion',
        'email',
        'telefono',
        'sobre_mi',
        'educacion',
        'objetivo',
        'valores',
        'conocimientos',
        'idiomas',
        'certificados',
        'habilidades',
        'logros',
        'proyectos_sociales',
        'color_plantilla',
        'fuente',
        'estado',
    ];

    /**
     * Un CV pertenece a un usuario (pasante).
     * Relación hacia la tabla 'usuarios' (no la tabla users de Laravel).
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }
}

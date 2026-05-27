<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacante extends Model
{
    protected $table = 'vacantes';

    public $timestamps = false;

    protected $fillable = [
        'empresa',
        'area',
        'descripcion',
        'estado',
        'creador_id',
    ];

    /**
     * Una vacante puede tener múltiples postulaciones.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class, 'vacante_id', 'id');
    }
}

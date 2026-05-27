<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Informe extends Model
{
    protected $table = 'informes';

    // Solo usa created_at por defecto según SQL de semilla, por lo que podemos desactivar el estándar de Laravel.
    public $timestamps = false;

    protected $fillable = [
        'pasante_id',
        'nombre',
        'tipo',
        'archivo_url',
        'estado',
        'horas',
        'observaciones',
        'objetivos',
        'actividades',
        'conclusiones',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Un informe pertenece a un pasante.
     */
    public function pasante(): BelongsTo
    {
        return $this->belongsTo(Pasante::class, 'pasante_id', 'id');
    }
}

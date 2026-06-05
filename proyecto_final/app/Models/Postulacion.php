<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    // Utiliza created_at en la BD
    public $timestamps = false;

    protected $fillable = [
        'pasante_id',
        'vacante_id',
        'estado',
        'cv_id',
    ];

    /**
     * Una postulación pertenece a un pasante.
     */
    public function pasante(): BelongsTo
    {
        return $this->belongsTo(Pasante::class, 'pasante_id', 'id');
    }

    /**
     * Una postulación pertenece a una vacante.
     */
    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class, 'vacante_id', 'id');
    }

    /**
     * Una postulación puede pertenecer a un CV.
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(CurriculumVitae::class, 'cv_id', 'id');
    }
}

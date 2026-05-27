<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecuperacionCodigo extends Model
{
    protected $table = 'recuperacion_codigos';

    protected $primaryKey = 'correo';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'correo',
        'codigo',
        'creado_en',
    ];
}

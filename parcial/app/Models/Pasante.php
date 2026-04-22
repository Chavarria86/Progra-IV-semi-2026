<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasante extends Model
{
    protected $fillable = ['nombre', 'carnet', 'carrera', 'email', 'telefono'];
}

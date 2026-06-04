<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $table = 'ai_mensajes';
    protected $fillable = ['chat_id', 'remitente', 'contenido', 'cv_id', 'informe_id', 'vacante_id'];

    public function chat()
    {
        return $this->belongsTo(AiChat::class, 'chat_id');
    }

    public function cv()
    {
        return $this->belongsTo(CurriculumVitae::class, 'cv_id');
    }

    public function informe()
    {
        return $this->belongsTo(Informe::class, 'informe_id');
    }

    public function vacante()
    {
        return $this->belongsTo(Vacante::class, 'vacante_id');
    }
}

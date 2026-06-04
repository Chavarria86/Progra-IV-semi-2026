<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    protected $table = 'ai_chats';
    protected $fillable = ['usuario_id', 'rol', 'titulo'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function mensajes()
    {
        return $this->hasMany(AiMessage::class, 'chat_id');
    }
}

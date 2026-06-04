<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ai_chats')) {
            Schema::create('ai_chats', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('usuario_id');
                $table->string('rol'); // pasante, supervisor, vice_decano
                $table->string('titulo')->default('Conversación con IA');
                $table->timestamps();

                $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('ai_mensajes')) {
            Schema::create('ai_mensajes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chat_id');
                $table->string('remitente'); // user, model
                $table->text('contenido');
                $table->timestamps();

                $table->foreign('chat_id')->references('id')->on('ai_chats')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_mensajes');
        Schema::dropIfExists('ai_chats');
    }
};

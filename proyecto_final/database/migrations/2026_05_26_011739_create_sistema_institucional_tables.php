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
        // 1. Tabla base de usuarios (usamos increments para que sea int(10) unsigned y coincida con CVs)
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo_institucional')->unique();
            $table->string('password');
            $table->string('estado')->default('activo');
            $table->string('rol')->default('pasante');
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 2. Personal Administrativo (Supervisores, Vicedecano)
        Schema::create('personal_administrativo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo_institucional')->unique();
            $table->string('password');
            $table->string('cargo'); // supervisor, vice_decano
        });

        // 3. Pasantes (Perfil extendido del usuario)
        Schema::create('pasantes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->string('area');
            $table->string('tipo_pasantia')->default('interna');
            $table->string('estado')->default('en_proceso'); // en_proceso, aprobado, rechazado
            $table->string('fase_actual')->default('Fase 1');
            $table->unsignedInteger('supervisor_id')->nullable(); // Relación con el supervisor asignado
            $table->timestamps();

            // Claves Foráneas
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('personal_administrativo')->onDelete('set null');
        });

        // 4. Vacantes disponibles
        Schema::create('vacantes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('empresa');
            $table->string('area');
            $table->text('descripcion');
            $table->string('estado')->default('activa');
        });

        // 5. Postulaciones (Relaciona Pasantes con Vacantes)
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pasante_id');
            $table->unsignedInteger('vacante_id');
            $table->string('estado')->default('pendiente');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pasante_id')->references('id')->on('pasantes')->onDelete('cascade');
            $table->foreign('vacante_id')->references('id')->on('vacantes')->onDelete('cascade');
        });

        // 6. Informes (Relaciona Pasantes con sus entregas)
        Schema::create('informes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pasante_id');
            $table->string('tipo'); // parcial, final
            $table->string('archivo_url');
            $table->string('estado')->default('pendiente'); // pendiente, aprobado, correccion
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pasante_id')->references('id')->on('pasantes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes');
        Schema::dropIfExists('postulaciones');
        Schema::dropIfExists('vacantes');
        Schema::dropIfExists('pasantes');
        Schema::dropIfExists('personal_administrativo');
        Schema::dropIfExists('usuarios');
    }
};

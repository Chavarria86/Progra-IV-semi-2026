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
        // 1. Añadir cv_id a postulaciones si no existe
        if (Schema::hasTable('postulaciones')) {
            Schema::table('postulaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('postulaciones', 'cv_id')) {
                    $table->unsignedInteger('cv_id')->nullable()->after('estado');
                    // No usamos constraint de foreign key fuerte aquí porque la migración
                    // de curriculum_vitae la crea como bigInteger o integer, 
                    // evitamos conflictos para que la comunicación no falle.
                }
            });
        }

        // 2. Crear tabla recomendaciones
        if (!Schema::hasTable('recomendaciones')) {
            Schema::create('recomendaciones', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pasante_id');
                $table->unsignedInteger('supervisor_id');
                $table->string('tipo')->default('satisfactorio');
                $table->string('titulo')->nullable();
                $table->text('contenido');
                $table->timestamps();

                $table->foreign('pasante_id')->references('id')->on('pasantes')->onDelete('cascade');
                $table->foreign('supervisor_id')->references('id')->on('usuarios')->onDelete('cascade');
            });
        }

        // 3. Crear tabla solicitudes_supervisor para guardar el historial
        if (!Schema::hasTable('solicitudes_supervisor')) {
            Schema::create('solicitudes_supervisor', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pasante_id');
                $table->unsignedInteger('supervisor_id');
                $table->text('mensaje')->nullable();
                $table->string('estado')->default('pendiente'); // pendiente, aceptada, rechazada
                $table->timestamps();

                $table->foreign('pasante_id')->references('id')->on('pasantes')->onDelete('cascade');
                $table->foreign('supervisor_id')->references('id')->on('usuarios')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_supervisor');
        Schema::dropIfExists('recomendaciones');

        if (Schema::hasTable('postulaciones') && Schema::hasColumn('postulaciones', 'cv_id')) {
            Schema::table('postulaciones', function (Blueprint $table) {
                $table->dropColumn('cv_id');
            });
        }
    }
};

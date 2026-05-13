<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_vitae', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla 'usuarios' (pasantes)
            // La tabla usuarios usa int(11), por eso usamos unsignedInteger
            $table->unsignedInteger('usuario_id')->unique(); // Un usuario = un CV

            // Archivo PDF
            $table->string('nombre_archivo');       // Ej: CV_Juan_Perez.pdf
            $table->string('ruta_archivo');         // Ej: cvs/1/CV_Juan_Perez.pdf
            $table->string('url_publica')->nullable(); // URL accesible

            // Metadatos del CV generado
            $table->string('nombre_completo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->text('sobre_mi')->nullable();
            $table->text('educacion')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('valores')->nullable();
            $table->text('conocimientos')->nullable();
            $table->text('idiomas')->nullable();
            $table->text('certificados')->nullable();
            $table->text('habilidades')->nullable();
            $table->text('logros')->nullable();
            $table->text('proyectos_sociales')->nullable();

            // Configuración de diseño elegida
            $table->string('color_plantilla')->default('#67000F');
            $table->string('fuente')->default('Montserrat');

            // Control
            $table->string('estado')->default('activo'); // activo, archivado
            $table->timestamps(); // created_at, updated_at

            /
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_vitae');
    }
};

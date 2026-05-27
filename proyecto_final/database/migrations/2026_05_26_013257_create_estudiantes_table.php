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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_estudiante')->unique(); // Ej: USSS027724
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('genero');
            $table->string('estado_civil');
            $table->string('dui')->unique();
            $table->text('direccion');
            $table->date('fecha_nacimiento');
            $table->string('departamento_nacimiento');
            $table->string('municipio_nacimiento');
            $table->string('pais');
            $table->string('correo_principal'); // Ej: emersonchavarria578@gmail.com
            $table->string('correo_secundario')->unique(); // Correo institucional Ej: usss027724@ugb.edu.sv
            $table->string('telefono')->nullable();
            $table->string('celular');
            $table->boolean('es_estudiante_activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};

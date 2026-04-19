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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->uuid('idMatricula');
            $table->string('idAlumno', 36);
            $table->string('idMateria', 36);
            $table->string('idDocente', 36);
            $table->date('fecha');
            $table->string('estado', 20);
            $table->string('periodo', 20);
            $table->integer('gestion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};

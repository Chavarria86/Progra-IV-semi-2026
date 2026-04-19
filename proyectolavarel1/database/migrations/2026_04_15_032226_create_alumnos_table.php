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
    Schema::create('alumnos', function (Blueprint $table) {
        $table->id(); // Equivale a tu 'id' int(11) AUTO_INCREMENT
        $table->uuid('idAlumno'); // Tu identificador UUID
        $table->string('codigo', 20); // char(20) en tu SQL
        $table->string('nombre', 100); // char(100) en tu SQL
        $table->string('direccion', 150); // char(150) en tu SQL
        $table->string('telefono', 15); // char(10) en tu SQL (puse 15 por si acaso)
        $table->text('email'); // text en tu SQL
        $table->timestamps(); // Agrega created_at y updated_at (estándar de Laravel)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};

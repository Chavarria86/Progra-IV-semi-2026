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
        if (!Schema::hasTable('recuperacion_codigos')) {
            Schema::create('recuperacion_codigos', function (Blueprint $table) {
                $table->string('correo')->primary();
                $table->string('codigo');
                $table->timestamp('creado_en')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recuperacion_codigos');
    }
};

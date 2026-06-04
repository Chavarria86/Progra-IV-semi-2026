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
        Schema::table('ai_mensajes', function (Blueprint $table) {
            $table->unsignedBigInteger('cv_id')->nullable();
            $table->unsignedInteger('informe_id')->nullable();
            $table->unsignedInteger('vacante_id')->nullable();

            $table->foreign('cv_id')->references('id')->on('curriculum_vitae')->onDelete('set null');
            $table->foreign('informe_id')->references('id')->on('informes')->onDelete('set null');
            $table->foreign('vacante_id')->references('id')->on('vacantes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_mensajes', function (Blueprint $table) {
            $table->dropForeign(['cv_id']);
            $table->dropForeign(['informe_id']);
            $table->dropForeign(['vacante_id']);

            $table->dropColumn(['cv_id', 'informe_id', 'vacante_id']);
        });
    }
};

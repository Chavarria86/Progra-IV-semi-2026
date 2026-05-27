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
        // Add 'horas' to 'informes' table
        if (Schema::hasTable('informes') && !Schema::hasColumn('informes', 'horas')) {
            Schema::table('informes', function (Blueprint $table) {
                $table->integer('horas')->default(0)->after('estado');
            });
        }

        // Add 'horas_aprobadas' to 'pasantes'
        if (Schema::hasTable('pasantes') && !Schema::hasColumn('pasantes', 'horas_aprobadas')) {
            Schema::table('pasantes', function (Blueprint $table) {
                $table->integer('horas_aprobadas')->default(0)->after('fase_actual');
            });
        }

        // Add 'creador_id' to 'vacantes'
        if (Schema::hasTable('vacantes') && !Schema::hasColumn('vacantes', 'creador_id')) {
            Schema::table('vacantes', function (Blueprint $table) {
                $table->unsignedInteger('creador_id')->nullable()->after('estado');
                // The creator is usually a supervisor or vicedecano, we'll link it to personal_administrativo
                $table->foreign('creador_id')->references('id')->on('personal_administrativo')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('informes') && Schema::hasColumn('informes', 'horas')) {
            Schema::table('informes', function (Blueprint $table) {
                $table->dropColumn('horas');
            });
        }

        if (Schema::hasTable('pasantes') && Schema::hasColumn('pasantes', 'horas_aprobadas')) {
            Schema::table('pasantes', function (Blueprint $table) {
                $table->dropColumn('horas_aprobadas');
            });
        }

        if (Schema::hasTable('vacantes') && Schema::hasColumn('vacantes', 'creador_id')) {
            Schema::table('vacantes', function (Blueprint $table) {
                $table->dropForeign(['creador_id']);
                $table->dropColumn('creador_id');
            });
        }
    }
};

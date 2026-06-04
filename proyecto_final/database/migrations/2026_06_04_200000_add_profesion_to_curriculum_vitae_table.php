<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_vitae', function (Blueprint $table) {
            $table->string('profesion')->nullable()->after('nombre_completo');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_vitae', function (Blueprint $table) {
            $table->dropColumn('profesion');
        });
    }
};

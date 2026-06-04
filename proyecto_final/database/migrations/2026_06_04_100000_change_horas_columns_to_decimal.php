<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En PostgreSQL, a veces cambiar de integer a decimal requiere un cast explícito.
        // Usamos raw SQL para asegurar que funcione perfectamente en pgsql.
        DB::statement('ALTER TABLE informes ALTER COLUMN horas TYPE DECIMAL(8,2) USING horas::decimal');
        DB::statement('ALTER TABLE pasantes ALTER COLUMN horas_aprobadas TYPE DECIMAL(8,2) USING horas_aprobadas::decimal');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE informes ALTER COLUMN horas TYPE INTEGER USING horas::integer');
        DB::statement('ALTER TABLE pasantes ALTER COLUMN horas_aprobadas TYPE INTEGER USING horas_aprobadas::integer');
    }
};

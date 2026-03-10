<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cambia la columna status de ENUM(draft, confirmed, shipped)
     * a ENUM(pending, shipped, cancelled).
     *
     * Los registros existentes con status = 'draft' o 'confirmed'
     * se migran a 'pending'. Los 'shipped' se mantienen.
     */
    public function up(): void
    {
        // 1. Normalizar valores viejos antes de cambiar el ENUM
        DB::statement("UPDATE packing_slips SET status = 'pending' WHERE status IN ('draft', 'confirmed')");

        // 2. Alterar el ENUM con los nuevos valores
        DB::statement("ALTER TABLE packing_slips MODIFY COLUMN status ENUM('pending', 'shipped', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Estado del PS: pending|shipped|cancelled'");
    }

    public function down(): void
    {
        // Revertir valores nuevos a los anteriores
        DB::statement("UPDATE packing_slips SET status = 'draft' WHERE status IN ('pending', 'cancelled')");

        DB::statement("ALTER TABLE packing_slips MODIFY COLUMN status ENUM('draft', 'confirmed', 'shipped') NOT NULL DEFAULT 'draft' COMMENT 'Estado del PS: draft|confirmed|shipped'");
    }
};

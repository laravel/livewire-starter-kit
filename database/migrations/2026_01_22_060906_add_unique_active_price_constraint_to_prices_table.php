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
        // Primero, desactivar precios duplicados activos del mismo tipo
        // manteniendo solo el más reciente por part_id y workstation_type
        DB::statement("
            UPDATE prices p1
            SET active = 0
            WHERE active = 1
            AND EXISTS (
                SELECT 1 FROM prices p2
                WHERE p2.part_id = p1.part_id
                AND p2.workstation_type = p1.workstation_type
                AND p2.active = 1
                AND p2.effective_date > p1.effective_date
            )
        ");

        // Agregar índice único para garantizar solo un precio activo por tipo de estación
        // MySQL no soporta índices parciales nativamente, usamos un workaround con trigger
        // Para MySQL 8.0.13+, podríamos usar índice funcional, pero usaremos trigger para compatibilidad
        
        // Crear trigger para validar unicidad antes de INSERT
        DB::unprepared("
            CREATE TRIGGER check_unique_active_price_before_insert
            BEFORE INSERT ON prices
            FOR EACH ROW
            BEGIN
                IF NEW.active = 1 THEN
                    IF EXISTS (
                        SELECT 1 FROM prices
                        WHERE part_id = NEW.part_id
                        AND workstation_type = NEW.workstation_type
                        AND active = 1
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Ya existe un precio activo para este tipo de estación de trabajo';
                    END IF;
                END IF;
            END
        ");

        // Crear trigger para validar unicidad antes de UPDATE
        DB::unprepared("
            CREATE TRIGGER check_unique_active_price_before_update
            BEFORE UPDATE ON prices
            FOR EACH ROW
            BEGIN
                IF NEW.active = 1 THEN
                    IF EXISTS (
                        SELECT 1 FROM prices
                        WHERE part_id = NEW.part_id
                        AND workstation_type = NEW.workstation_type
                        AND active = 1
                        AND id != NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Ya existe un precio activo para este tipo de estación de trabajo';
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar triggers
        DB::unprepared('DROP TRIGGER IF EXISTS check_unique_active_price_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS check_unique_active_price_before_update');
    }
};

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
        Schema::table('lots', function (Blueprint $table) {
            // Status de calidad
            $table->string('quality_status')->default('pending')
                  ->after('status')
                  ->comment('pending, approved, rejected');

            // Comentarios de calidad (motivo de rechazo, observaciones)
            $table->text('quality_comments')->nullable()
                  ->after('quality_status');

            // Fecha de inspeccion
            $table->timestamp('quality_inspected_at')->nullable()
                  ->after('quality_comments');

            // Usuario que inspecciono
            $table->foreignId('quality_inspected_by')->nullable()
                  ->after('quality_inspected_at')
                  ->constrained('users')
                  ->nullOnDelete();

            // Indice para consultas frecuentes
            $table->index('quality_status');
            $table->index(['work_order_id', 'quality_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['quality_inspected_by']);
            $table->dropIndex(['quality_status']);
            $table->dropIndex(['work_order_id', 'quality_status']);
            $table->dropColumn([
                'quality_status',
                'quality_comments',
                'quality_inspected_at',
                'quality_inspected_by',
            ]);
        });
    }
};

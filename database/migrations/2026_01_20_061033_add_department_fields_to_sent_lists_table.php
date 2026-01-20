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
        Schema::table('sent_lists', function (Blueprint $table) {
            // Workflow fields
            $table->string('current_department')->default('materiales')->after('status')
                ->comment('Departamento actual: materiales, produccion, calidad, envios');
            
            $table->json('department_history')->nullable()->after('current_department')
                ->comment('Historial de transiciones entre departamentos');
            
            $table->timestamp('materials_approved_at')->nullable()->after('department_history');
            $table->foreignId('materials_approved_by')->nullable()->constrained('users')->after('materials_approved_at');
            
            $table->timestamp('production_approved_at')->nullable()->after('materials_approved_by');
            $table->foreignId('production_approved_by')->nullable()->constrained('users')->after('production_approved_at');
            
            $table->timestamp('quality_approved_at')->nullable()->after('production_approved_by');
            $table->foreignId('quality_approved_by')->nullable()->constrained('users')->after('quality_approved_at');
            
            $table->timestamp('shipping_approved_at')->nullable()->after('quality_approved_by');
            $table->foreignId('shipping_approved_by')->nullable()->constrained('users')->after('shipping_approved_at');
            
            $table->text('notes')->nullable()->after('shipping_approved_by')
                ->comment('Notas generales de la lista preliminar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sent_lists', function (Blueprint $table) {
            $table->dropForeign(['materials_approved_by']);
            $table->dropForeign(['production_approved_by']);
            $table->dropForeign(['quality_approved_by']);
            $table->dropForeign(['shipping_approved_by']);
            
            $table->dropColumn([
                'current_department',
                'department_history',
                'materials_approved_at',
                'materials_approved_by',
                'production_approved_at',
                'production_approved_by',
                'quality_approved_at',
                'quality_approved_by',
                'shipping_approved_at',
                'shipping_approved_by',
                'notes',
            ]);
        });
    }
};

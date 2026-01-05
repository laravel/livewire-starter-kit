<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Unifica empleados y usuarios en una sola tabla.
     * Los empleados ahora son usuarios con rol 'employee'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos de empleado
            $table->string('employee_number')->unique()->nullable()->after('account');
            $table->string('position')->nullable()->after('employee_number');
            $table->date('birth_date')->nullable()->after('position');
            $table->date('entry_date')->nullable()->after('birth_date');
            $table->text('comments')->nullable()->after('entry_date');
            $table->boolean('active')->default(true)->after('comments');
            
            // Relaciones
            $table->foreignId('area_id')->nullable()->after('active')->constrained('areas')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('area_id')->constrained('shifts')->nullOnDelete();
            
            // Soft deletes para usuarios
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['shift_id']);
            
            $table->dropColumn([
                'employee_number',
                'position',
                'birth_date',
                'entry_date',
                'comments',
                'active',
                'area_id',
                'shift_id',
                'deleted_at',
            ]);
        });
    }
};

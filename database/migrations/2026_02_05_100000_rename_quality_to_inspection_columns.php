<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Renames all quality-related columns to inspection across multiple tables.
     */
    public function up(): void
    {
        // =====================================================
        // 1. LOTS TABLE - Rename quality columns to inspection
        // =====================================================
        Schema::table('lots', function (Blueprint $table) {
            // Rename quality_status to inspection_status
            $table->renameColumn('quality_status', 'inspection_status');

            // Rename quality_comments to inspection_comments
            $table->renameColumn('quality_comments', 'inspection_comments');

            // Rename quality_inspected_at to inspection_completed_at
            $table->renameColumn('quality_inspected_at', 'inspection_completed_at');

            // Rename quality_inspected_by to inspection_completed_by
            $table->renameColumn('quality_inspected_by', 'inspection_completed_by');
        });

        // Check if final_quality columns exist before renaming
        if (Schema::hasColumn('lots', 'final_quality_status')) {
            Schema::table('lots', function (Blueprint $table) {
                $table->renameColumn('final_quality_status', 'final_inspection_status');
                $table->renameColumn('final_quality_comments', 'final_inspection_comments');
                $table->renameColumn('final_quality_inspected_at', 'final_inspection_completed_at');
                $table->renameColumn('final_quality_inspected_by', 'final_inspection_completed_by');
            });
        }

        // =====================================================
        // 2. SENT_LISTS TABLE - Rename quality columns
        // =====================================================
        if (Schema::hasColumn('sent_lists', 'quality_approved_at')) {
            Schema::table('sent_lists', function (Blueprint $table) {
                $table->renameColumn('quality_approved_at', 'inspection_approved_at');
                $table->renameColumn('quality_approved_by', 'inspection_approved_by');
            });
        }

        // Update current_department values from 'calidad' to 'inspeccion'
        DB::table('sent_lists')
            ->where('current_department', 'calidad')
            ->update(['current_department' => 'inspeccion']);

        // =====================================================
        // 3. KITS TABLE - Rename quality columns
        // =====================================================
        if (Schema::hasColumn('kits', 'submitted_to_quality_at')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->renameColumn('submitted_to_quality_at', 'submitted_to_inspection_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // =====================================================
        // 1. LOTS TABLE - Restore quality columns
        // =====================================================
        Schema::table('lots', function (Blueprint $table) {
            $table->renameColumn('inspection_status', 'quality_status');
            $table->renameColumn('inspection_comments', 'quality_comments');
            $table->renameColumn('inspection_completed_at', 'quality_inspected_at');
            $table->renameColumn('inspection_completed_by', 'quality_inspected_by');
        });

        if (Schema::hasColumn('lots', 'final_inspection_status')) {
            Schema::table('lots', function (Blueprint $table) {
                $table->renameColumn('final_inspection_status', 'final_quality_status');
                $table->renameColumn('final_inspection_comments', 'final_quality_comments');
                $table->renameColumn('final_inspection_completed_at', 'final_quality_inspected_at');
                $table->renameColumn('final_inspection_completed_by', 'final_quality_inspected_by');
            });
        }

        // =====================================================
        // 2. SENT_LISTS TABLE - Restore quality columns
        // =====================================================
        if (Schema::hasColumn('sent_lists', 'inspection_approved_at')) {
            Schema::table('sent_lists', function (Blueprint $table) {
                $table->renameColumn('inspection_approved_at', 'quality_approved_at');
                $table->renameColumn('inspection_approved_by', 'quality_approved_by');
            });
        }

        // Restore current_department values
        DB::table('sent_lists')
            ->where('current_department', 'inspeccion')
            ->update(['current_department' => 'calidad']);

        // =====================================================
        // 3. KITS TABLE - Restore quality columns
        // =====================================================
        if (Schema::hasColumn('kits', 'submitted_to_inspection_at')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->renameColumn('submitted_to_inspection_at', 'submitted_to_quality_at');
            });
        }
    }
};

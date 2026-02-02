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
        Schema::table('kits', function (Blueprint $table) {
            $table->timestamp('submitted_to_quality_at')->nullable()->after('released_by');
            $table->timestamp('approved_at')->nullable()->after('submitted_to_quality_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null');
            $table->integer('current_approval_cycle')->default(1)->after('approved_by');

            // Indexes
            $table->index('submitted_to_quality_at');
            $table->index('approved_at');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kits', function (Blueprint $table) {
            $table->dropIndex(['submitted_to_quality_at']);
            $table->dropIndex(['approved_at']);
            $table->dropIndex(['approved_by']);
            
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'submitted_to_quality_at',
                'approved_at',
                'approved_by',
                'current_approval_cycle'
            ]);
        });
    }
};

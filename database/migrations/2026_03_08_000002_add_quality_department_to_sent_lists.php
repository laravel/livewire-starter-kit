<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sent_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('sent_lists', 'quality_approved_at')) {
                $table->timestamp('quality_approved_at')->nullable()->after('inspection_approved_by');
            }
            if (!Schema::hasColumn('sent_lists', 'quality_approved_by')) {
                $table->foreignId('quality_approved_by')
                    ->nullable()
                    ->after('quality_approved_at')
                    ->constrained('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sent_lists', function (Blueprint $table) {
            $table->dropForeign(['quality_approved_by']);
            $table->dropColumn(['quality_approved_at', 'quality_approved_by']);
        });
    }
};

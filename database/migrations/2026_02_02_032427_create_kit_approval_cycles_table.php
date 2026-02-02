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
        Schema::create('kit_approval_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained()->onDelete('cascade');
            $table->integer('cycle_number');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['kit_id', 'cycle_number']);
            $table->index('status');
            $table->index('submitted_by');
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_approval_cycles');
    }
};

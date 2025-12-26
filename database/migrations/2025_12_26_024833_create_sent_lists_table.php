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
        Schema::create('sent_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->json('shift_ids');
            $table->integer('num_persons');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_available_hours', 10, 2);
            $table->decimal('used_hours', 10, 2);
            $table->decimal('remaining_hours', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('po_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });

        // Create pivot table for sent_list_shift
        Schema::create('sent_list_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_list_id')->constrained('sent_lists')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['sent_list_id', 'shift_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_list_shift');
        Schema::dropIfExists('sent_lists');
    }
};

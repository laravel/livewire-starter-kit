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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('lot_number');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['work_order_id', 'lot_number']);
            $table->index(['work_order_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};

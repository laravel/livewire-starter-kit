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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('part_id')->constrained()->onDelete('restrict');
            $table->date('po_date');
            $table->date('due_date');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 4);
            $table->string('status')->default('pending'); // pending, approved, rejected, pending_correction
            $table->text('comments')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'po_date']);
            $table->index(['part_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

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
        Schema::create('sent_list_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_list_id')->constrained('sent_lists')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->integer('quantity')->comment('Cantidad del PO incluida en esta lista');
            $table->decimal('required_hours', 10, 2)->default(0)->comment('Horas requeridas calculadas');
            $table->string('lot_number')->nullable()->comment('Número de lote/viajero asignado');
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['sent_list_id', 'purchase_order_id'], 'sent_list_po_unique');
            
            // Indexes for performance
            $table->index('sent_list_id');
            $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_list_purchase_orders');
    }
};

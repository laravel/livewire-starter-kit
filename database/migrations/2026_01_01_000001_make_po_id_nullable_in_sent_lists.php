<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * El Capacity Wizard puede crear SentLists sin PO asociado,
     * ya que es una herramienta de planificación de capacidad.
     */
    public function up(): void
    {
        Schema::table('sent_lists', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['po_id']);
            
            // Make po_id nullable
            $table->foreignId('po_id')->nullable()->change();
            
            // Re-add the foreign key constraint (now allowing null)
            $table->foreign('po_id')
                ->references('id')
                ->on('purchase_orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sent_lists', function (Blueprint $table) {
            $table->dropForeign(['po_id']);
            $table->foreignId('po_id')->nullable(false)->change();
            $table->foreign('po_id')
                ->references('id')
                ->on('purchase_orders')
                ->onDelete('cascade');
        });
    }
};

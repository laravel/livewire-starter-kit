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
        Schema::create('weighings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('kit_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity')->comment('Cantidad total del lote (solo visual)');
            $table->integer('good_pieces')->default(0)->comment('Piezas buenas');
            $table->integer('bad_pieces')->default(0)->comment('Piezas malas');
            $table->timestamp('weighed_at')->comment('Fecha y hora de la pesada');
            $table->foreignId('weighed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weighings');
    }
};

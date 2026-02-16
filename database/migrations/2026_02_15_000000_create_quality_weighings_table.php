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
        Schema::create('quality_weighings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('kit_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('production_good_pieces')->comment('Total de piezas buenas de produccion (referencia)');
            $table->integer('good_pieces')->default(0)->comment('Piezas aprobadas por calidad');
            $table->integer('bad_pieces')->default(0)->comment('Piezas rechazadas por calidad');
            $table->string('disposition')->default('rework')->comment('rework o scrap');
            $table->string('rework_status')->nullable()->comment('pending_rework, in_rework, rework_complete');
            $table->timestamp('weighed_at')->comment('Fecha y hora de la pesada de calidad');
            $table->foreignId('weighed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('lot_id');
            $table->index('rework_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_weighings');
    }
};

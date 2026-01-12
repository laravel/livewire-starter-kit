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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            
            // Precio de muestra (antes unit_price)
            $table->decimal('sample_price', 10, 4);
            
            // Tipo de estación de trabajo
            $table->enum('workstation_type', ['table', 'machine', 'semi_automatic'])->default('table');
            
            $table->date('effective_date');
            $table->boolean('active')->default(true);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['part_id', 'active', 'effective_date']);
            $table->index(['workstation_type']);
        });

        // Tabla pivote para los niveles de precio por volumen
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('min_quantity');
            $table->unsignedInteger('max_quantity')->nullable(); // null = sin límite (ej: 100000+)
            $table->decimal('tier_price', 10, 4);
            $table->timestamps();

            $table->index(['price_id', 'min_quantity']);
            $table->unique(['price_id', 'min_quantity', 'max_quantity'], 'price_tier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
        Schema::dropIfExists('prices');
    }
};

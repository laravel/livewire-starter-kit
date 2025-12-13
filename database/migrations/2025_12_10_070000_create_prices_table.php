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
            $table->decimal('unit_price', 10, 4);
            $table->decimal('tier_1_999', 10, 4)->nullable();
            $table->decimal('tier_1000_10999', 10, 4)->nullable();
            $table->decimal('tier_11000_99999', 10, 4)->nullable();
            $table->decimal('tier_100000_plus', 10, 4)->nullable();
            $table->date('effective_date');
            $table->boolean('active')->default(true);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['part_id', 'active', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};

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
        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('kit_number')->unique();
            $table->string('status')->default('preparing'); // preparing, ready, released, in_assembly
            $table->boolean('validated')->default(false);
            $table->text('validation_notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('released_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['work_order_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kits');
    }
};

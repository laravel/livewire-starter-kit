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
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->time('start_break_time')->unique();
            $table->time('end_break_time')->unique();
            $table->tinyInteger('active')->default(1);
            $table->text('comments')->nullable();

            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};

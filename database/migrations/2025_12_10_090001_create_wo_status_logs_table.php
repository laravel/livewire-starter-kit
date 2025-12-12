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
        Schema::create('wo_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_status_id')->nullable()->constrained('statuses_wo')->onDelete('set null');
            $table->foreignId('to_status_id')->constrained('statuses_wo')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wo_status_logs');
    }
};

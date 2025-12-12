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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('restrict');
            $table->foreignId('status_id')->constrained('statuses_wo')->onDelete('restrict');
            $table->integer('sent_pieces')->default(0);
            $table->date('scheduled_send_date')->nullable();
            $table->date('actual_send_date')->nullable();
            $table->date('opened_date');
            $table->string('eq')->nullable(); // Equipment
            $table->string('pr')->nullable(); // Personnel
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_id', 'opened_date']);
            $table->index(['purchase_order_id']);
            $table->index(['scheduled_send_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

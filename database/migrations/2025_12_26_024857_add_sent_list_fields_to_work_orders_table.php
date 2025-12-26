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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('sent_list_id')->nullable()->after('purchase_order_id')
                  ->constrained('sent_lists')->onDelete('set null');
            $table->enum('assembly_mode', ['1_person', '2_persons', '3_persons'])
                  ->nullable()
                  ->after('sent_list_id');
            $table->decimal('required_hours', 10, 2)->nullable()->after('assembly_mode');

            $table->index('sent_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['sent_list_id']);
            $table->dropIndex(['sent_list_id']);
            $table->dropColumn(['sent_list_id', 'assembly_mode', 'required_hours']);
        });
    }
};

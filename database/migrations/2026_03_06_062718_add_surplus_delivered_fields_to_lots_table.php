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
        Schema::table('lots', function (Blueprint $table) {
            $table->boolean('surplus_delivered')->default(false)->after('surplus_received_by');
            $table->timestamp('surplus_delivered_at')->nullable()->after('surplus_delivered');
            $table->unsignedBigInteger('surplus_delivered_by')->nullable()->after('surplus_delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn(['surplus_delivered', 'surplus_delivered_at', 'surplus_delivered_by']);
        });
    }
};

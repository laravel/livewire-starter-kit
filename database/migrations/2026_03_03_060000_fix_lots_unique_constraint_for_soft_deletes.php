<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replace the unique(work_order_id, lot_number) constraint with one that
     * allows the same lot_number to exist if the previous record was soft-deleted.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropUnique(['work_order_id', 'lot_number']);
        });

        // MySQL doesn't support partial/filtered unique indexes natively.
        // Use a unique index on (work_order_id, lot_number, deleted_at) instead.
        // Since deleted_at is NULL for active records and a timestamp for deleted ones,
        // this effectively allows re-use of lot_number after soft-delete.
        DB::statement('CREATE UNIQUE INDEX lots_wo_lot_number_deleted_unique ON lots (work_order_id, lot_number, deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropIndex('lots_wo_lot_number_deleted_unique');
            $table->unique(['work_order_id', 'lot_number']);
        });
    }
};

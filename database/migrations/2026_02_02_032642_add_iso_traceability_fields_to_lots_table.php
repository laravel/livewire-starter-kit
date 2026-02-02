<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            if (!Schema::hasColumn('lots', 'raw_material_batch_numbers')) {
                $table->json('raw_material_batch_numbers')->nullable()->after('comments');
            }
            if (!Schema::hasColumn('lots', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('raw_material_batch_numbers');
            }
            if (!Schema::hasColumn('lots', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('lots', 'receipt_date')) {
                $table->date('receipt_date')->nullable()->after('supplier_name');
            }
            if (!Schema::hasColumn('lots', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('receipt_date');
            }
        });

        // Add indexes using raw SQL to avoid errors if they already exist
        try {
            DB::statement('CREATE INDEX lots_supplier_id_index ON lots (supplier_id)');
        } catch (\Exception $e) {
            // Index already exists, ignore
        }
        
        try {
            DB::statement('CREATE INDEX lots_receipt_date_index ON lots (receipt_date)');
        } catch (\Exception $e) {
            // Index already exists, ignore
        }
        
        try {
            DB::statement('CREATE INDEX lots_expiration_date_index ON lots (expiration_date)');
        } catch (\Exception $e) {
            // Index already exists, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        try {
            DB::statement('DROP INDEX lots_supplier_id_index ON lots');
        } catch (\Exception $e) {
            // Index doesn't exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX lots_receipt_date_index ON lots');
        } catch (\Exception $e) {
            // Index doesn't exist, ignore
        }
        
        try {
            DB::statement('DROP INDEX lots_expiration_date_index ON lots');
        } catch (\Exception $e) {
            // Index doesn't exist, ignore
        }

        Schema::table('lots', function (Blueprint $table) {
            $columns = ['raw_material_batch_numbers', 'supplier_id', 'supplier_name', 'receipt_date', 'expiration_date'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('lots', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

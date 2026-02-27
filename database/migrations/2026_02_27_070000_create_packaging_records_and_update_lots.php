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
        Schema::create('packaging_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kit_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('available_pieces');
            $table->integer('packed_pieces');
            $table->integer('surplus_pieces');
            $table->integer('adjusted_surplus')->nullable();
            $table->string('adjustment_reason')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('packed_at');
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lots', function (Blueprint $table) {
            // Viajero
            $table->boolean('viajero_received')->default(false)->after('packaging_inspected_at');
            $table->timestamp('viajero_received_at')->nullable()->after('viajero_received');
            $table->unsignedBigInteger('viajero_received_by')->nullable()->after('viajero_received_at');

            // Closure decision (Control de Materiales)
            $table->string('closure_decision')->nullable()->after('viajero_received_by');
            $table->unsignedBigInteger('closure_decided_by')->nullable()->after('closure_decision');
            $table->timestamp('closure_decided_at')->nullable()->after('closure_decided_by');

            // Surplus received
            $table->boolean('surplus_received')->default(false)->after('closure_decided_at');
            $table->timestamp('surplus_received_at')->nullable()->after('surplus_received');
            $table->unsignedBigInteger('surplus_received_by')->nullable()->after('surplus_received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_records');

        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn([
                'viajero_received',
                'viajero_received_at',
                'viajero_received_by',
                'closure_decision',
                'closure_decided_by',
                'closure_decided_at',
                'surplus_received',
                'surplus_received_at',
                'surplus_received_by',
            ]);
        });
    }
};

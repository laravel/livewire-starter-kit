<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_list_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_list_id')->constrained()->onDelete('cascade');
            $table->string('from_department'); // quién rechaza: inspeccion, calidad
            $table->string('to_department');   // a dónde regresa: materiales, produccion
            $table->foreignId('rejected_by')->constrained('users')->onDelete('restrict');
            $table->text('reason');
            $table->foreignId('lot_id')->nullable()->constrained('lots')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('sent_list_id');
            $table->index(['sent_list_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_list_rejections');
    }
};

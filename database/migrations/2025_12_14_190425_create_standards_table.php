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
        Schema::create('standards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');

            $table->integer('persons_1')->nullable();
            $table->integer('persons_2')->nullable();
            $table->integer('persons_3')->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['area_id', 'active'], 'standards_search_index');
            $table->index(['department_id', 'active'], 'standards_dept_active_index');
            $table->index('effective_date', 'standards_effective_date_index');
            $table->index('active', 'standards_active_index');
            $table->index('part_id', 'standards_part_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standards');
    }
};

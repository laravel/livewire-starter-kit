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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('number')->unique();
            $table->string('position')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('entry_date')->nullable();
            $table->tinyInteger('active')->default(1)->comment('1: Activo, 0: Inactivo');
            $table->string('comments')->nullable();

            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

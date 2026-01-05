<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina la tabla employees ya que los empleados ahora
     * están unificados en la tabla users con rol 'employee'.
     */
    public function up(): void
    {
        Schema::dropIfExists('employees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
            $table->tinyInteger('active')->default(1);
            $table->string('comments')->nullable();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};

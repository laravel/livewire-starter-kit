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
        Schema::create('skills', function (Blueprint $table) {
            $table->increments('id')->primary();
            $table->string('group');
            $table->unsignedInteger('group_rank')->default(0);
            $table->string('skill')->nullable();
            $table->unsignedInteger('skill_rank')->default(0);
            $table->string('description')->nullable();
            $table->unsignedInteger('level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};

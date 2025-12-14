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
        Schema::create('classGroup', function (Blueprint $table) {
            $table->string('classGroupId', 10)->primary();
            $table->integer('maxCapacity')->nullable();
            $table->integer('currentEnrollment')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classGroup');
    }
};

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
        Schema::create('replacement', function (Blueprint $table) {
            $table->string('replacementId', 10)->primary();
            $table->string('appealId', 10);
            $table->string('userId', 10);
            $table->string('classGroupId', 10);
            $table->date('date');
            $table->time('timeSlot');
            $table->timestamps();

            $table->foreign('appealId')->references('appealId')->on('replacementAppeal')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('classGroupId')->references('classGroupId')->on('classGroup')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replacement');
    }
};

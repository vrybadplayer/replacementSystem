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
            // Primary Key
            $table->string('replacementId', 10)->primary();
            
            // Foreign Key Columns
            $table->string('appealId', 10)->index();
            $table->string('userId', 10)->index();
            $table->string('requestedClassGroupId', 10)->index();
            $table->string('originalTimetableId', 10)->index();
            
            // Date Logic
            $table->date('originalDate'); 
            $table->date('requestedDate');
            
            // Time Logic
            $table->time('originalStartTime');
            $table->time('startTime');
            $table->time('endTime');
            
            $table->timestamps();

            // Constraints
            $table->foreign('appealId')->references('appealId')->on('replacementAppeal')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('requestedClassGroupId')->references('classGroupId')->on('classGroup')->onDelete('cascade');
            
            $table->foreign('originalTimetableId')->references('timetableId')->on('timetable')->onDelete('cascade');
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
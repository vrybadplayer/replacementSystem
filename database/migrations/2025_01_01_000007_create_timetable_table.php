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
        Schema::create('timetable', function (Blueprint $table) {
            $table->string('timetableId', 10)->primary();
            $table->string('classGroupId', 10);
            $table->string('courseSubjectId', 10);
            $table->string('classType', 8)->nullable();
            $table->string('userId', 10);
            $table->string('venueId', 10);
            $table->string('dayOfWeek', 9)->nullable();
            $table->time('startTime');
            $table->time('endTime');
            $table->date('classDate');
            $table->string('classMode', 12)->nullable();
            $table->timestamps();

            $table->foreign('classGroupId')->references('classGroupId')->on('classGroup')->onDelete('cascade');
            $table->foreign('venueId')->references('venueId')->on('venue')->onDelete('cascade');
            $table->foreign('courseSubjectId')->references('courseSubjectId')->on('course_subject')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable');
    }
};

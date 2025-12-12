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
        Schema::create('course_subject', function (Blueprint $table) {
            $table->string('courseSubjectId', 10)->primary();
            $table->string('courseId', 10);
            $table->string('subjectId', 10);
            $table->timestamps();

            $table->foreign('courseId')->references('courseId')->on('course')->onDelete('cascade');
            $table->foreign('subjectId')->references('subjectId')->on('subject')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_subject');
    }
};

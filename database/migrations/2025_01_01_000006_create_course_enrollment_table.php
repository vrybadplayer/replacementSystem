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
        Schema::create('course_enrollment', function (Blueprint $table) {
            $table->string('enrollmentID', 10)->primary();
            $table->string('userID', 10);
            $table->string('courseID', 10);
            $table->integer('year')->nullable();
            $table->integer('semester')->nullable();
            $table->timestamps();

            $table->foreign('userID')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('courseID')->references('courseId')->on('course')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollment');
    }
};

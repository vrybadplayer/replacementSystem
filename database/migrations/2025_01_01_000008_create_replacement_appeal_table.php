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
        Schema::create('replacementAppeal', function (Blueprint $table) {
            $table->string('appealId', 10)->primary();
            $table->string('userId', 10);
            $table->string('originalClassId', 10);
            $table->string('requestedClassId', 10);
            $table->time('originalTimeslot');
            $table->time('requestedTimeslot');
            $table->text('reason');
            $table->string('proof', 255)->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->dateTime('submissionDate');
            $table->dateTime('decisionDate')->nullable();
            $table->string('adminId', 10)->nullable();
            $table->text('adminRemarks')->nullable();
            $table->timestamps();

            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('originalClassId')->references('timetableId')->on('timetable')->onDelete('cascade');
            $table->foreign('requestedClassId')->references('timetableId')->on('timetable')->onDelete('cascade');
            $table->foreign('adminId')->references('userId')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replacementAppeal');
    }
};

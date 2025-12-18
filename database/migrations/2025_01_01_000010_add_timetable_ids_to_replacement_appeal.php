<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('replacementAppeal', function (Blueprint $table) {
            $table->string('originalTimetableId', 10)->nullable()->after('userId');
            $table->string('requestedTimetableId', 10)->nullable()->after('originalTimetableId');
            
            $table->foreign('originalTimetableId')->references('timetableId')->on('timetable')->onDelete('set null');
            $table->foreign('requestedTimetableId')->references('timetableId')->on('timetable')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('replacementAppeal', function (Blueprint $table) {
            $table->dropForeign(['originalTimetableId']);
            $table->dropForeign(['requestedTimetableId']);
            $table->dropColumn(['originalTimetableId', 'requestedTimetableId']);
        });
    }
};

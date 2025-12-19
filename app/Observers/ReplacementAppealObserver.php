<?php

namespace App\Observers;

use App\Models\ReplacementAppeal;
use App\Models\Replacement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReplacementAppealObserver
{
    // auto generate ID and set defaults
    public function creating(ReplacementAppeal $replacementAppeal): void
    {
        if (empty($replacementAppeal->appealId)) {
            $replacementAppeal->appealId = 'APP' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }

        // Set submission date if not provided
        if (empty($replacementAppeal->submissionDate)) {
            $replacementAppeal->submissionDate = now();
        }

        // Default status is Pending
        if (empty($replacementAppeal->status)) {
            $replacementAppeal->status = 'Pending';
        }
    }

    // log when appeal created
    public function created(ReplacementAppeal $replacementAppeal): void
    {
        Log::info("New replacement appeal created: {$replacementAppeal->appealId} by user {$replacementAppeal->userId}");
    }

    // set decision date when approving
    public function updating(ReplacementAppeal $replacementAppeal): void
    {
        // Check if status is being changed to Approved
        if ($replacementAppeal->isDirty('status') && $replacementAppeal->status === 'Approved') {
            // Set decision date
            $replacementAppeal->decisionDate = now();
        }
    }

    // create replacement record when approved
    public function updated(ReplacementAppeal $replacementAppeal): void
    {
        // When appeal is approved, create replacement record
        if ($replacementAppeal->status === 'Approved' && !$replacementAppeal->replacement) {
            // Load user and get timetables directly using stored IDs
            $replacementAppeal->load('user');
            $user = $replacementAppeal->user;
            
            // Get timetable records using the stored timetableIds
            $originalTimetable = \App\Models\Timetable::find($replacementAppeal->originalTimetableId);
            $requestedTimetable = \App\Models\Timetable::find($replacementAppeal->requestedTimetableId);
            
            if ($user && $requestedTimetable && $originalTimetable) {
                Replacement::create([
                    'replacementId' => 'REP' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'appealId' => $replacementAppeal->appealId,
                    'userId' => $replacementAppeal->userId,
                    'requestedClassGroupId' => $replacementAppeal->requestedClassGroupId,
                    'originalTimetableId' => $originalTimetable->timetableId,
                    'originalDate' => $originalTimetable->classDate,
                    'requestedDate' => $requestedTimetable->classDate,
                    'originalStartTime' => $originalTimetable->startTime,
                    'startTime' => $requestedTimetable->startTime,
                    'endTime' => $requestedTimetable->endTime,
                ]);

                Log::info("Replacement created for approved appeal: {$replacementAppeal->appealId}");
            } else {
                Log::error("Could not find user or timetable data for appeal: {$replacementAppeal->appealId}");
            }
        }

        // When appeal is rejected
        if ($replacementAppeal->status === 'Rejected') {
            Log::info("Appeal rejected: {$replacementAppeal->appealId}");
        }
    }

    // log when deleted
    public function deleted(ReplacementAppeal $replacementAppeal): void
    {
        Log::info("Replacement appeal deleted: {$replacementAppeal->appealId}");
    }

}

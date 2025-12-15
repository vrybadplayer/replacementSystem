<?php

namespace App\Services;

use App\Models\Timetable;
use Carbon\Carbon;

class TimetableService
{
    public function getTimetablesByClassGroupAndDateRange($classGroupId, $startDate, $endDate)
    {
        return Timetable::with(['venue', 'courseSubject'])
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                      ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->whereBetween('classDate', [$startDate, $endDate])
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();
    }
    
    public function getTimetablesByStudentAndWeek($studentId, $selectedDate)
    {
        $selectedDate = Carbon::parse($selectedDate);
        $startOfWeek = $selectedDate->startOfWeek()->format('Y-m-d');
        $endOfWeek = $selectedDate->endOfWeek()->format('Y-m-d');
        
        // Note: You'll need to get classGroupId from user/student somehow
        // This depends on your data structure
        $classGroupId = $this->getClassGroupIdByStudentId($studentId);
        
        if (!$classGroupId) {
            return collect([]);
        }
        
        return $this->getTimetablesByClassGroupAndDateRange($classGroupId, $startOfWeek, $endOfWeek);
    }
    
    protected function getClassGroupIdByStudentId($studentId)
    {
        // Implement based on your user/student relationship
        $user = \App\Models\User::find($studentId);
        return $user->classGroupId ?? null;
    }
}
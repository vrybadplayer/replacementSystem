<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAllCourseSubjects()
 * @method static \App\Models\CourseSubject|null getCourseSubjectById(string $courseSubjectId)
 * @method static \App\Models\CourseSubject createOrUpdateCourseSubject(array $data, string $courseSubjectId = null)
 * @method static \App\Models\CourseSubject createCourseSubject(array $data)
 * @method static \App\Models\CourseSubject updateCourseSubject(string $courseSubjectId, array $data)
 * @method static bool deleteCourseSubject(string $courseSubjectId)
 * 
 * @see \App\Services\CourseSubjectService
 */
class CourseSubjectFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'courseSubject.service';
    }
}


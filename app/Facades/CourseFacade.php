<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAllCourses()
 * @method static \App\Models\Course|null getCourseById(string $courseId)
 * @method static \App\Models\Course createOrUpdateCourse(array $data, string $courseId = null)
 * @method static \App\Models\Course createCourse(array $data)
 * @method static \App\Models\Course updateCourse(string $courseId, array $data)
 * @method static bool deleteCourse(string $courseId)
 * @method static array getCoursesForView()
 * 
 * @see \App\Services\CourseService
 */
class CourseFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'course.service';
    }
}


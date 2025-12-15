<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Unified Facade for Course Subject Management Module
 * 
 * Handles all operations related to Courses, Subjects, and CourseSubject relationships
 * 
 * Course Methods:
 * @method static \Illuminate\Database\Eloquent\Collection getAllCourses()
 * @method static \App\Models\Course|null getCourseById(string $courseId)
 * @method static \App\Models\Course createOrUpdateCourse(array $data, string $courseId = null)
 * @method static \App\Models\Course createCourse(array $data)
 * @method static \App\Models\Course updateCourse(string $courseId, array $data)
 * @method static bool deleteCourse(string $courseId)
 * @method static array getCoursesForView()
 * 
 * Subject Methods:
 * @method static \Illuminate\Database\Eloquent\Collection getAllSubjects()
 * @method static \App\Models\Subject|null getSubjectById(string $subjectId)
 * @method static \App\Models\Subject createOrUpdateSubject(array $data, string $subjectId = null)
 * @method static \App\Models\Subject createSubject(array $data)
 * @method static \App\Models\Subject updateSubject(string $subjectId, array $data)
 * @method static bool deleteSubject(string $subjectId)
 * @method static array getSubjectsForView()
 * @method static \Illuminate\Database\Eloquent\Collection fetchSubjectsWithCourses(array|null $filters = null) Fetch subjects with courses relationship
 * @method static \Illuminate\Database\Eloquent\Collection prepareCourseCodes(\Illuminate\Database\Eloquent\Collection $subjects) Optionally prepare course codes for subjects
 * @method static array getSubjectsWithCoursesForView(array|null $filters = null) Single point to get subjects list with course information
 * 
 * CourseSubject Methods:
 * @method static \Illuminate\Database\Eloquent\Collection getAllCourseSubjects()
 * @method static \App\Models\CourseSubject|null getCourseSubjectById(string $courseSubjectId)
 * @method static \App\Models\CourseSubject createOrUpdateCourseSubject(array $data, string $courseSubjectId = null)
 * @method static \App\Models\CourseSubject createCourseSubject(array $data)
 * @method static \App\Models\CourseSubject updateCourseSubject(string $courseSubjectId, array $data)
 * @method static bool deleteCourseSubject(string $courseSubjectId)
 * 
 * API-Based Methods:
 * @method static array fetchCoursesFromApi() Fetch courses from API endpoint
 * @method static array getCoursesForViewFromApi() Fetch, process, and format courses from API
 * @method static array fetchSubjectsFromApi() Fetch subjects from API endpoint
 * @method static array getSubjectsForViewFromApi() Fetch, process, and format subjects from API
 * 
 * @see \App\Services\CourseService
 * @see \App\Services\SubjectService
 * @see \App\Services\CourseSubjectService
 * @see \App\Services\CourseSubjectManagementService
 */
class CourseSubjectManagementFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'courseManagement.service';
    }
}


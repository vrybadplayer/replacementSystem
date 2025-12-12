<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'course';
    protected $primaryKey = 'courseId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'courseId',
        'courseCode',
        'courseName',
        'faculty',
        'status'
    ];

    // Relationships
    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'courseId', 'courseId');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'course_subject', 'courseId', 'subjectId', 'courseId', 'subjectId');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'courseID', 'courseId');
    }
}

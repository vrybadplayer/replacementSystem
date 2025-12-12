<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSubject extends Model
{
    protected $table = 'course_subject';
    protected $primaryKey = 'courseSubjectId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'courseSubjectId',
        'courseId',
        'subjectId'
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId', 'courseId');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectId', 'subjectId');
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'courseSubjectId', 'courseSubjectId');
    }
}

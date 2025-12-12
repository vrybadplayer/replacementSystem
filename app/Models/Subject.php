<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subject';
    protected $primaryKey = 'subjectId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'subjectId',
        'subjectCode',
        'subjectName',
        'creditHours',
        'category',
        'status'
    ];

    // Relationships
    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'subjectId', 'subjectId');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_subject', 'subjectId', 'courseId', 'subjectId', 'courseId');
    }
}
